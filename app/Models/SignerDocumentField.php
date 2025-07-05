<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SignerDocumentField extends Model implements Lockable, Ownable
{
    use HasFactory, ProtectsLockedModels;

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

    public function isLocked(): bool
    {
        return $this->documentSigner?->document?->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    public function validateModification(string $method, array $options): bool
    {
        // change only when its status is draft:
        if ($this->isDirty('document_signer_id')) {
            $status = $this->documentSigner?->document?->getOriginal('status');
            if ($status !== DocumentStatus::DRAFT) {
                return false;
            }
        }

        // change only when its status is template or draft:
        $templateOrDraftFields = [
            'page', 'x', 'y', 'width', 'height', 'type', 'label', 'description', 'required'
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
} 