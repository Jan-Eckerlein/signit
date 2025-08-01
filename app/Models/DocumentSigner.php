<?php

namespace App\Models;

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
use App\Builders\DocumentSignerBuilder;
use App\Exceptions\ValidateModelModificationFailedException;
use Illuminate\Database\Eloquent\HasBuilder;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

// ---------------------------- PROPERTIES ----------------------------

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $document_id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $signature_completed_at
 * @property int|null $electronic_signature_disclosure_accepted
 * @property \Carbon\Carbon|null $disclosure_accepted_at
 */
class DocumentSigner extends Model implements Lockable, Ownable, Validatable
{
    /** @use HasFactory<\Database\Factories\DocumentSignerFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\DocumentSignerBuilder> */
    use HasBuilder;
    use ProtectsLockedModels, ValidatesModelModifications;

    protected static string $builder = DocumentSignerBuilder::class;

    protected $fillable = [
        'document_id',
        'user_id',
        'name',
        'description',
        'signature_completed_at',
        'electronic_signature_disclosure_accepted',
        'disclosure_accepted_at',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<DocumentField, $this> */
    public function documentFields(): HasMany
    {
        return $this->hasMany(DocumentField::class);
    }

    // ---------------------------- LOCKING ----------------------------

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return in_array(
            $this->document->getOriginal('status'), 
            [DocumentStatus::COMPLETED],
            true
        );
    }

    // ---------------------------- VALIDATION ----------------------------

    /** @return bool */
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        $allowedFields = [
            DocumentStatus::DRAFT->value => [
                'name',
                'description',
                'document_id',
                'user_id',
            ],
            DocumentStatus::OPEN->value => [
                'signature_completed_at',
                'electronic_signature_disclosure_accepted',
                'disclosure_accepted_at',
            ],
            DocumentStatus::IN_PROGRESS->value => [
                'signature_completed_at',
                'electronic_signature_disclosure_accepted',
                'disclosure_accepted_at',
            ],
        ];

        $allowedFields = $allowedFields[$this->document->getOriginal('status')->value];

        $dirty = $this->getDirty();

        foreach ($dirty as $field => $value) {
            if (!in_array($field, $allowedFields)) {
                throw new ValidateModelModificationFailedException(static::class, "Field {$field} is not allowed to be modified");
            }
        }
        
        // Check if signature completion fields are being modified
        $signatureCompletionFields = [
            'signature_completed_at',
            'electronic_signature_disclosure_accepted', 
            'disclosure_accepted_at'
        ];
        
        $isModifyingSignatureCompletion = false;
        foreach ($signatureCompletionFields as $field) {
            if ($this->isDirty($field)) {
                $isModifyingSignatureCompletion = true;
                break;
            }
        }
        
        if ($isModifyingSignatureCompletion) {
            // Check if all fields are completed
            $completedFields = $this->getCompletedFieldsCount();
            $totalFields = $this->getTotalFieldsCount();
            
            if ($completedFields < $totalFields) {
                throw new UnprocessableEntityHttpException(
                    "Cannot complete signature: {$completedFields} of {$totalFields} fields are filled. All fields must be completed before signature can be finalized."
                );
            }
        }
        
        // Original validation for document_id changes
        if (!$this->isDirty('document_id') || !$this->exists) return true;

        $from = $this->getOriginal('document_id');
        $to = $this->document_id;

        return $from === $to;
    }

    // ---------------------------- OWNERSHIP ----------------------------

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->document->isOwnedBy($user);
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document->isViewableBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create document signers for documents they own
        $document = Document::find($attributes['document_id']);
        return $document && $document->isOwnedBy($user);
    }
    
    // ---------------------------- UTILITIES ----------------------------


    /** @return bool */
    public function isSignatureCompleted(): bool
    {
        return $this->signature_completed_at !== null;
    }

    /** @return int */
    public function getCompletedFieldsCount(): int
    {
        return $this->documentFields()
            ->whereHas('value')
            ->count();
    }

    /** @return int */
    public function getTotalFieldsCount(): int
    {
        return $this->documentFields()->count();
    }

    public function completeSignature(): void
    {
        $this->update([
            'signature_completed_at' => now(),
            'electronic_signature_disclosure_accepted' => true,
            'disclosure_accepted_at' => now(),
        ]);
    }
} 