<?php

namespace App\Policies;

use App\Contracts\Ownable;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class OwnablePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view lists of their own models
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ownable $model): bool
    {
        return $model->isViewableBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // Users can create models for themselves
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ownable $model): bool
    {
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ownable $model): bool
    {
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ownable $model): bool
    {
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ownable $model): bool
    {
        return $model->isOwnedBy($user);
    }
} 