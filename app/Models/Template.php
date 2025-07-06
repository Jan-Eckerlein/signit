<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Template extends Model implements Ownable
{
    use HasFactory;

    protected $fillable = [
        'title',
        'owner_user_id',
        'description',
    ];

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function templateSigners(): HasMany
    {
        return $this->hasMany(TemplateSigner::class);
    }

    public function templateFields(): HasMany
    {
        return $this->hasMany(TemplateField::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->owner_user_id === ($user ? $user->id : Auth::id());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where('owner_user_id', $user->id);
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where('owner_user_id', $user->id);
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create templates for themselves
        return $attributes['owner_user_id'] === $user->id;
    }
} 