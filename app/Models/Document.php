<?php

namespace App\Models;

use App\Builders\DocumentBuilder;
use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements Ownable<self>
 * @property int $id
 * @property string $title
 * @property int $owner_user_id
 * @property string $description
 * @property int $template_document_id
 * @property DocumentStatus $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Document extends Model implements Lockable, Ownable, Validatable
{
    /** @use HasFactory<\Database\Factories\DocumentFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\DocumentBuilder> */
    use HasBuilder;
    use ProtectsLockedModels, ValidatesModelModifications;

    protected static string $builder = DocumentBuilder::class;

    protected $fillable = [
        'title',
        'owner_user_id',
        'description',
        'template_document_id',
        'status',
    ];

    protected $casts = [
        'status' => DocumentStatus::class,
    ];

    // ---------------------------- LOCKING ----------------------------

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<User, $this> */
    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<DocumentSigner, $this> */
    public function documentSigners(): HasMany
    {
        return $this->hasMany(DocumentSigner::class);
    }

    /** @return HasMany<DocumentLog, $this> */
    public function documentLogs(): HasMany
    {
        return $this->hasMany(DocumentLog::class);
    }

    /** @return HasMany<DocumentPage, $this> */
    public function documentPages(): HasMany
    {
        return $this->hasMany(DocumentPage::class);
    }

    /** @return HasOne<PdfProcess, $this> */
    public function pdfProcess(): HasOne
    {
        return $this->hasOne(PdfProcess::class);
    }

    /** @return HasManyThrough<DocumentField, DocumentPage, $this> */
    public function documentFields(): HasManyThrough
    {
        return $this->hasManyThrough(DocumentField::class, DocumentPage::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function templateDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'template_document_id');
    }


    // ---------------------------- VALIDATION ----------------------------

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        Log::info('running validateModification');
        if (!$this->isDirty('status')) return true;

        
        // If the document is new, only allow 'template' or 'draft' status
        if (!$this->exists) {
            if (!in_array($this->status, [DocumentStatus::TEMPLATE, DocumentStatus::DRAFT], strict: true)) {
                throw new \Exception('New documents must be created as template or draft');
            }
            return true;
        }

        Log::info('validateModification', ['event' => $event, 'options' => $options]);

        /** @var DocumentStatus $from */
        $from = $this->getOriginal('status');
        $to = $this->status;

        $validTransitions = [
            // Draft documents can be opened
            DocumentStatus::DRAFT->value => [DocumentStatus::OPEN->value],
            // Open documents can be send back to draft or marked as in progress
            DocumentStatus::OPEN->value => [DocumentStatus::DRAFT->value, DocumentStatus::IN_PROGRESS->value],
            // In progress documents can be completed
            DocumentStatus::IN_PROGRESS->value => [DocumentStatus::COMPLETED->value],
            // Template documents cannot change the status
            DocumentStatus::TEMPLATE->value => [],
            //! Completed documents are not editable at all
            DocumentStatus::COMPLETED->value => [],
        ];

        // Check if transition is valid
        if (!in_array($to->value, $validTransitions[$from->value], strict: true)) {
            throw new UnprocessableEntityHttpException('Invalid status transition from ' . $from->value . ' to ' . $to->value);
        }

        // Additional validation for IN_PROGRESS transition
        if ($to === DocumentStatus::OPEN) {
            $this->validateOpenForSigningTransition();
        }

        return true;
    }

    private function validateOpenForSigningTransition(): void
    {
        // Check if document has signers
        if ($this->documentSigners()->count() === 0) {
            throw new UnprocessableEntityHttpException('Document #' . $this->id . ' has no signers');
        }

        // Check if all signers are bound to a user
        /** @var int $unboundSigners */
        $unboundSigners = $this->documentSigners()->whereNull('user_id')->count();
        if ($unboundSigners > 0) {
            $signers = $this->documentSigners()->whereNull('user_id')->get();   
            $signerNames = $signers->map(function (DocumentSigner $signer) {
                return $signer->name;
            })->implode(', ');
            throw new UnprocessableEntityHttpException('There are ' . $unboundSigners . ' unbound signers (no user assigned) in this document. Please assign a user to the signers: ' . $signerNames);
        }

        // Check if all signers have at least one field
        $signersWithoutFieldsCount = $this->documentSigners()
            ->whereDoesntHave('documentFields')
            ->count();
        
        if ($signersWithoutFieldsCount > 0) {
            $signersWithoutFieldIds = $this->documentSigners()->whereDoesntHave('documentFields')->pluck('id')->implode(', ');
            throw new UnprocessableEntityHttpException('There are ' . $signersWithoutFieldsCount . ' signers without fields in this document. Please assign fields to the signers: ' . $signersWithoutFieldIds);
        }

        // Check if all fields are bound to signers (no unbound fields)
        $unboundFields = $this->documentFields()
            ->whereNull('document_signer_id')
            ->count();
        
        if ($unboundFields > 0) {
            throw new UnprocessableEntityHttpException('Unbound fields: ' . $unboundFields);
        }
    }


    // ---------------------------- OWNERSHIP ----------------------------


    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->owner_user_id === ($user ? $user->id : Auth::id());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return 
            $this->isOwnedBy($user) || 
            (
                $this->isStatus(DocumentStatus::OPEN, DocumentStatus::IN_PROGRESS, DocumentStatus::COMPLETED) &&
                $this->documentSigners()->where('user_id', $user ? $user->id : Auth::id())->exists()
            );
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create documents for themselves
        Log::info('canCreateThis', ['attributes' => $attributes, 'user' => $user]);
        return $attributes['owner_user_id'] === $user->id;
    }


    // ---------------------------- UTILITIES ----------------------------

    /**
     * @param DocumentStatus $statuses
     */
    public function isStatus(...$statuses): bool
    {
        $statusValues = array_map(fn(DocumentStatus $status) => $status->value, $statuses);

        return in_array($this->getOriginal('status')->value, $statusValues, strict: true);
    }

    public function statusIsOpenOrLater(): bool
    {
        return $this->isStatus(DocumentStatus::OPEN, DocumentStatus::IN_PROGRESS, DocumentStatus::COMPLETED);
    }

    public function areAllSignersCompleted(): bool
    {
        return $this->documentSigners()
            ->whereNull('signature_completed_at')
            ->doesntExist();
    }
} 