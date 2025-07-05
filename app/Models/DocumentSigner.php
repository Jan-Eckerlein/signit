<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class DocumentSigner extends Model implements Lockable, Ownable
{
    use HasFactory, ProtectsLockedModels;

    protected $fillable = [
        'document_id',
        'user_id',
    ];

    public function isLocked(): bool
    {
        return in_array(
            $this->document->getOriginal('status'), 
            [DocumentStatus::IN_PROGRESS, DocumentStatus::OPEN],
            true
        );
    }

    public function validateModification(string $method, array $options): bool
    {
        if (!$this->isDirty('document_id')) return true;

        $from = $this->getOriginal('document_id');
        $to = $this->document_id;

        return $from === $to;
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signerDocumentFields(): HasMany
    {
        return $this->hasMany(SignerDocumentField::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->document->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }
} 