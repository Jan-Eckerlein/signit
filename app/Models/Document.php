<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Document extends Model implements Lockable, Ownable, Validatable
{
    use HasFactory, ProtectsLockedModels, ValidatesModelModifications;

    protected $fillable = [
        'title',
        'owner_user_id',
        'description',
        'template_document_id',
    ];

    protected $guarded = ['status'];

    protected $casts = [
        'status' => DocumentStatus::class,
    ];

    // ---------------------------- LOCKING ----------------------------

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    // ---------------------------- RELATIONS ----------------------------

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /** @return HasMany<DocumentSigner, $this> */
    public function documentSigners(): HasMany
    {
        return $this->hasMany(DocumentSigner::class);
    }

    public function documentLogs(): HasMany
    {
        return $this->hasMany(DocumentLog::class);
    }

    public function documentPages(): HasMany
    {
        return $this->hasMany(DocumentPage::class);
    }

    public function signerDocumentFields(): HasMany
    {
        return $this->hasMany(SignerDocumentField::class);
    }

    public function templateDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'template_document_id');
    }


    // ---------------------------- VALIDATION ----------------------------

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        if (!$this->isDirty('status')) return true;

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

        // If the document is new, only allow 'template' or 'draft' status
        if (!$this->exists && !in_array($this->status, [DocumentStatus::TEMPLATE, DocumentStatus::DRAFT], strict: true)) {
            throw new \Exception('New documents must be created as template or draft');
        }

        // Check if transition is valid
        if (!in_array($to->value, $validTransitions[$from->value], strict: true)) {
            throw new \Exception('Invalid status transition from ' . $from->value . ' to ' . $to->value);
        }

        // Additional validation for IN_PROGRESS transition
        if ($to === DocumentStatus::IN_PROGRESS) {
            return $this->validateInProgressTransition();
        }

        return true;
    }

    private function validateInProgressTransition(): bool
    {
        // Check if document has signers
        if ($this->documentSigners()->count() === 0) {
            throw new \Exception('Document #' . $this->id . ' has no signers');
        }

        // Check if all signers are bound to a user
        /** @var int $unboundSigners */
        $unboundSigners = $this->documentSigners()->whereNull('user_id')->count();
        if ($unboundSigners > 0) {
            $signers = $this->documentSigners()->whereNull('user_id')->get();   
            $signerNames = $signers->map(function ($signer) {
                return $signer->name;
            })->implode(', ');
            throw new \Exception('There are ' . $unboundSigners . ' unbound signers (no user assigned) in this document. Please assign a user to the signers: ' . $signerNames);
        }

        // Check if all signers have at least one field
        $signersWithoutFieldsCount = $this->documentSigners()
            ->whereDoesntHave('signerDocumentFields')
            ->count();
        
        if ($signersWithoutFieldsCount > 0) {
            $signersWithoutFieldIds = $this->documentSigners()->whereDoesntHave('signerDocumentFields')->pluck('id')->implode(', ');
            throw new \Exception('There are ' . $signersWithoutFieldsCount . ' signers without fields in this document. Please assign fields to the signers: ' . $signersWithoutFieldIds);
        }

        // Check if all fields are bound to signers (no unbound fields)
        $unboundFields = $this->signerDocumentFields()
            ->whereNull('document_signer_id')
            ->count();
        
        if ($unboundFields > 0) {
            throw new \Exception('Unbound fields: ' . $unboundFields);
        }

        return true;
    }


    // ---------------------------- OWNERSHIP ----------------------------


    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->owner_user_id === ($user ? $user->id : Auth::id());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user) || $this->documentSigners()->where('user_id', $user ? $user->id : Auth::id())->exists();
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where('owner_user_id', $user->id);
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where(function (Builder $query) use ($user) {
            $query
                // EIGENE Dokumente immer
                ->where('owner_user_id', $user->id)
                
                // FREMDE nur wenn sie "offen", "in progress" oder "completed" sind
                ->orWhere(function (Builder $query) use ($user) {
                    $query->whereIn('status', [
                            DocumentStatus::OPEN,
                            DocumentStatus::IN_PROGRESS,
                            DocumentStatus::COMPLETED,
                        ])
                        ->whereHas('documentSigners', function (Builder $q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                });
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create documents for themselves
        return $attributes['owner_user_id'] === $user->id;
    }


    // ---------------------------- UTILITIES ----------------------------

    /**
     * @param DocumentStatus $statuses
     */
    public function isStatus(...$statuses): bool
    {
        $statusValues = array_map(fn(DocumentStatus $status) => $status->value, $statuses);

        return in_array($this->status->value, $statusValues, strict: true);
    }

    public function scopeWithIncompleteSigners(Builder $query): Builder
    {
        return $query->whereHas('documentSigners', function ($query) {
            $query->whereNull('signature_completed_at');
        });
    }

    public function areAllSignersCompleted(): bool
    {
        return $this->documentSigners()
            ->whereNull('signature_completed_at')
            ->doesntExist();
    }

    public function getProgress(): array
    {
        return [
            'total_signers' => $this->documentSigners()->count(),
            'completed_signers' => $this->documentSigners()
                ->whereNotNull('signature_completed_at')
                ->count(),
            'signers_progress' => $this->documentSigners()
                ->with(['user', 'signerDocumentFields.value'])
                ->get()
                ->map(function ($signer) {
                    return [
                        'id' => $signer->id,
                        'user_name' => $signer->user->name,
                        'completed_fields' => $signer->getCompletedFieldsCount(),
                        'total_fields' => $signer->getTotalFieldsCount(),
                        'is_completed' => $signer->isSignatureCompleted(),
                        'completed_at' => $signer->signature_completed_at,
                    ];
                }),
        ];
    }
} 