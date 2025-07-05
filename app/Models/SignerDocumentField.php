<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SignerDocumentField extends Model implements Lockable, Ownable, Validatable
{
    use HasFactory, ProtectsLockedModels, ValidatesModelModifications;

    protected $fillable = [
        'document_signer_id',
        'page',
        'x',
        'y',
        'width',
        'height',
        'type',
        'label',
        'description',
        'required',
    ];

    protected $casts = [
        'type' => DocumentFieldType::class,
        'required' => 'boolean',
    ];

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->documentSigner?->document?->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        // change only when its status is template or draft:
        $templateOrDraftFields = [
            'document_signer_id', 'page', 'x', 'y', 'width', 'height', 'type', 'label', 'description', 'required'
        ];
        foreach ($templateOrDraftFields as $field) {
            if ($this->isDirty($field)) {
                $status = $this->documentSigner?->document?->getOriginal('status');
                if (!in_array($status, [DocumentStatus::TEMPLATE, DocumentStatus::DRAFT], true)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    public function value(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SignerDocumentFieldValue::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->documentSigner?->document?->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->documentSigner?->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create signer document fields for documents they own
        $documentSigner = DocumentSigner::find($attributes['document_signer_id']);
        return $documentSigner && $documentSigner->document->isOwnedBy($user);
    }
} 