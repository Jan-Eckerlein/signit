<?php

namespace App\Models;

use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Contact extends Model implements Ownable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        return $query->where('user_id', $user ? $user->id : Auth::id());
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->user_id === ($user ? $user->id : Auth::id());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    public function scopeViewableBy(Builder $query, User $user): Builder
    {
        return $this->ownedBy($user);
    }
} 