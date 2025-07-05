<?php

namespace App\Policies\Composables;

use App\Contracts\Ownable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;


trait HandlesOwnable
{
    protected function bootHandlesOwnable()    {
        if (!method_exists($this, 'register')) {
            throw new \LogicException(static::class . ' must extend ComposablePolicy to use HandlesOwnable.');
        }
        $this->register('viewAny', $this->canViewAny(...));
        $this->register('view', $this->canView(...));
        $this->register('create', $this->canCreate(...));
        $this->register('update', $this->canUpdate(...));
        $this->register('delete', $this->canDelete(...));
        $this->register('restore', $this->canRestore(...));
        $this->register('forceDelete', $this->canForceDelete(...));
    }

    protected function checkOwnable(Model $model): void
    {
        if (!$model instanceof Ownable) {
            throw new \LogicException(static::class . ' must extend Ownable to use HandlesOwnable.');
        }
    }

    /**
     * Determine whether the user can view any models.
     */
    protected function canViewAny(User $user): bool
    {
        return true; // Users can view lists of their own models
    }

    /**
     * Determine whether the user can view the model.
     */
    protected function canView(User $user, Model $model): bool
    {
        $this->checkOwnable($model);
        return $model->isViewableBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    protected function canCreate(User $user): bool
    {
        // Get the model class from the policy name
        $modelClass = str_replace('Policy', '', static::class);
        $modelClass = str_replace('App\\Policies\\', 'App\\Models\\', $modelClass);
        
        // Check if the model class exists and implements Ownable
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, \App\Contracts\Ownable::class)) {
            throw new \LogicException(static::class . ' must extend Ownable to use HandlesOwnable.');
        }
        
        // Use the canCreateThis method with request attributes
        return $modelClass::canCreateThis($user, request()->all());
    }

    /**
     * Determine whether the user can update the model.
     */
    protected function canUpdate(User $user, Model $model): bool
    {
        $this->checkOwnable($model);
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    protected function canDelete(User $user, Model $model): bool
    {
        $this->checkOwnable($model);
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    protected function canRestore(User $user, Model $model): bool
    {
        $this->checkOwnable($model);
        return $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    protected function canForceDelete(User $user, Model $model): bool
    {
        $this->checkOwnable($model);
        return $model->isOwnedBy($user);
    }
} 