<?php

namespace App\Contracts;

use App\Models\User;

/**
 * This interface is used to mark models that are owned by a user.
 * 
 * Classes implementing this interface must use the HasBuilder trait.
 * 
 * 
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 */
interface Ownable
{
    public function isOwnedBy(User | null $user = null): bool;
    public function isViewableBy(User | null $user = null): bool;

    /**
     * @param array<model-property<TModelClass>, mixed> $attributes
     */
    public static function canCreateThis(User $user, array $attributes): bool;
} 