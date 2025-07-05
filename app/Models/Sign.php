<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Sign extends Model implements Lockable, Ownable
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

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->signerDocumentFieldValues()->exists();
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->user_id === $user->id;
    }

    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->isOwnedBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where('user_id', $user->id);
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        return $this->scopeOwnedBy($query, $user);
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create signs for themselves
        return $attributes['user_id'] === $user->id;
    }
} 