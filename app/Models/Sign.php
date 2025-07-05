<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Enums\DeletionStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sign extends Model implements Lockable
{
    use HasFactory, SoftDeletes, ProtectsLockedModels;

    protected $allowSoftDeletes = true;

    protected $fillable = [
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signerDocumentFieldValues(): HasMany
    {
        return $this->hasMany(SignerDocumentFieldValue::class, 'value_signature_sign_id');
    }

    public function isLocked(): bool
    {
        return $this->signerDocumentFieldValues()->exists();
    }

    public function validateModification(string $method, array $options): bool
    {
        return true;
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        return $query->where('user_id', $user ? $user->id : Auth::id());
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->user_id === ($user ? $user->id : Auth::id());
    }
} 