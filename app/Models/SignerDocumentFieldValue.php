<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SignerDocumentFieldValue extends Model implements Lockable, Ownable
{
    use HasFactory, ProtectsLockedModels;

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
        $isEditable = $this->signerDocumentField?->documentSigner?->document?->getOriginal('status') === DocumentStatus::IN_PROGRESS;
        return !$isEditable || $this->exists;
    }

    public function signerDocumentField(): BelongsTo
    {
        return $this->belongsTo(SignerDocumentField::class);
    }

    public function signatureSign(): BelongsTo
    {
        return $this->belongsTo(Sign::class, 'value_signature_sign_id');
    }


    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->signerDocumentField?->documentSigner?->user?->is($user ?? Auth::user());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('signerDocumentField.documentSigner.user', function (Builder $query) use ($user) {
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
        $signerDocumentField = SignerDocumentField::find($attributes['signer_document_field_id'])->with('documentSigner.user')->first();
        return $signerDocumentField?->documentSigner?->user?->is($user);
    }
}
