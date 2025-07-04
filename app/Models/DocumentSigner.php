<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentSigner extends Model implements Lockable
{
    use HasFactory, ProtectsLockedModels;

    protected $fillable = [
        'document_id',
        'contact_id',
    ];

    public function isLocked(): bool
    {
        return $this->document->isLocked();
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

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function signerDocumentFields(): HasMany
    {
        return $this->hasMany(SignerDocumentField::class);
    }

    public function scopeViewableBy(Builder $query, User $user): Builder
    {
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public function isMine(User $user): bool
    {
        return $this->document->isMine($user);
    }

    public function iAmSigner(User $user): bool
    {
        return $this->contact->knows_user_id === $user->id;
    }
} 