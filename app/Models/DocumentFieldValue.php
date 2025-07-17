<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Builders\DocumentFieldValueBuilder;
use App\Services\DocumentFieldValueValidationService;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\HasBuilder;

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $document_field_id
 * @property int|null $value_signature_sign_id
 * @property string|null $value_initials
 * @property string|null $value_text
 * @property bool|null $value_checkbox
 * @property \Carbon\Carbon|null $value_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class DocumentFieldValue extends Model implements Lockable, Ownable, Validatable
{
    /** @use HasFactory<\Database\Factories\DocumentFieldValueFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\DocumentFieldValueBuilder> */
    use HasBuilder;
    use ProtectsLockedModels, ValidatesModelModifications;

    protected static string $builder = DocumentFieldValueBuilder::class;

    protected $fillable = [
        'document_field_id',
        'value_signature_sign_id',
        'value_initials',
        'value_text',
        'value_checkbox',
        'value_date',
    ];

    protected $casts = [
        'value_checkbox' => 'boolean',
        'value_date' => 'date',
    ];

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        $isEditable = $this->documentField?->documentSigner?->document?->getOriginal('status') === DocumentStatus::IN_PROGRESS;
        return !$isEditable || $this->exists;
    }

    /** @return BelongsTo<DocumentField, $this> */
    public function documentField(): BelongsTo
    {
        return $this->belongsTo(DocumentField::class);
    }

    /** @return BelongsTo<Sign, $this> */
    public function signatureSign(): BelongsTo
    {
        return $this->belongsTo(Sign::class, 'value_signature_sign_id');
    }

    public function isCompleted(): bool
    {
        return $this->value_signature_sign_id !== null || $this->value_initials !== null || $this->value_text !== null || $this->value_checkbox !== null || $this->value_date !== null;
    }

    /** @return bool */
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        // Only validate on creation or when value fields are being modified
        if ($event === BaseModelEvent::CREATING || $this->isDirty(['value_signature_sign_id', 'value_initials', 'value_text', 'value_checkbox', 'value_date'])) {
            return $this->validateValueMatchesFieldType();
        }
        
        return true;
    }
    
    /** @return bool */
    private function validateValueMatchesFieldType(): bool
    {
        $field = $this->documentField;
        
        if (!$field) {
            return true; // Let the foreign key constraint handle this
        }
        
        // Use the shared validation service
        DocumentFieldValueValidationService::validateValueMatchesFieldType(
            $this->getAttributes(),
            $field->type
        );
        
        return true;
    }
    



    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->documentField?->documentSigner?->user?->is($user ?? Auth::user());
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        $documentField = DocumentField::with('documentSigner.user')->find($attributes['document_field_id']);
        return $documentField?->documentSigner?->user?->is($user);
    }
}
