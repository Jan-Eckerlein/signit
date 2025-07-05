<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface Ownable
{
    public function isOwnedBy(User | null $user = null): bool;
    public function isViewableBy(User | null $user = null): bool;
    public static function canCreateThis(User $user, array $attributes): bool;

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder;
    public function scopeViewableBy(Builder $query, User | null $user = null): Builder;
} 