<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface Ownable
{
    public function isOwnedBy(User | null $user = null): bool;
    public function isViewableBy(User | null $user = null): bool;

    public function scopeOwnedBy(Builder $query, User $user): Builder;
    public function scopeViewableBy(Builder $query, User $user): Builder;
} 