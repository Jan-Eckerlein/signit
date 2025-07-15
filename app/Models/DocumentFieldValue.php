<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Services\DocumentFieldValueValidationService;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class DocumentFieldValue extends Model implements Lockable, Ownable, Validatable
{
    use HasFactory, ProtectsLockedModels, ValidatesModelModifications;

    protected $fillable = [
        'signer_document_field_id',
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

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        $isEditable = $this->documentField?->documentSigner?->document?->getOriginal('status') === DocumentStatus::IN_PROGRESS;
        return !$isEditable || $this->exists;
    }

    public function documentField(): BelongsTo
    {
        return $this->belongsTo(DocumentField::class);
    }

    public function signatureSign(): BelongsTo
    {
        return $this->belongsTo(Sign::class, 'value_signature_sign_id');
    }

    public function isCompleted(): bool
    {
        return $this->value_signature_sign_id !== null || $this->value_initials !== null || $this->value_text !== null || $this->value_checkbox !== null || $this->value_date !== null;
    }

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        // Only validate on creation or when value fields are being modified
        if ($event === BaseModelEvent::CREATING || $this->isDirty(['value_signature_sign_id', 'value_initials', 'value_text', 'value_checkbox', 'value_date'])) {
            return $this->validateValueMatchesFieldType();
        }
        
        return true;
    }
    
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
    



    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->documentField?->documentSigner?->user?->is($user ?? Auth::user());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('documentField.documentSigner.user', function (Builder $query) use ($user) {
            $query->is($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->ownedBy($user);
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        $documentField = DocumentField::find($attributes['signer_document_field_id'])->with('documentSigner.user')->first();
        return $documentField?->documentSigner?->user?->is($user);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotNull('value_signature_sign_id')
                ->orWhereNotNull('value_initials')
                ->orWhereNotNull('value_text')
                ->orWhereNotNull('value_checkbox')
                ->orWhereNotNull('value_date');
        });
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('value_signature_sign_id')
            ->whereNull('value_initials')
            ->whereNull('value_text')
            ->whereNull('value_checkbox')
            ->whereNull('value_date');
    }
}
