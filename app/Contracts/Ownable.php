<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * This interface is used to mark models that are owned by a user.
 * 
 * Classes implementing this interface must use the HasBuilder trait.
 */
interface Ownable
{
    public function isOwnedBy(User | null $user = null): bool;
    public function isViewableBy(User | null $user = null): bool;
    public static function canCreateThis(User $user, array $attributes): bool;
} 