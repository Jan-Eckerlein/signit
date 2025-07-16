<?php

namespace App\Policies\Composables;

use App\Contracts\Ownable;
use App\Contracts\OwnablePolicy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin ComposablePolicy
 * @mixin OwnablePolicy
 * 
 * @template TModelClass of \Illuminate\Database\Eloquent\Model&\App\Contracts\Ownable
 * @phpstan-require-implements \App\Contracts\OwnablePolicy
 */
trait HandlesOwnable
{
    protected function bootHandlesOwnable(): void
    {
        if (!$this instanceof ComposablePolicy) {
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

    protected function checkMagicLinkUsedAndAllowed(string $action): bool
    {
        return session('auth_via_magic_link') && $this->isActionAllowedForMagicLink($action);
    }
    
    protected function getModelClass(): string
    {
        
        // Get the model class from the policy name
        $modelClass = str_replace('Policy', '', static::class);
        $modelClass = str_replace('App\\Policies\\', 'App\\Models\\', $modelClass);
        
        // Check if the model class exists and implements Ownable
        if (!class_exists($modelClass) || !is_subclass_of($modelClass, \App\Contracts\Ownable::class)) {
            throw new \LogicException(static::class . ' must extend Ownable to use HandlesOwnable.');
        }

        return $modelClass;
    }

    protected function checkOwnablePolicy(): void
    {
        if (!$this instanceof OwnablePolicy) {
            throw new \LogicException(static::class . ' must implement OwnablePolicy to use magic link authorization.');
        }
    }

    /**
     * Check if an action is allowed for magic link users
     */
    public function isActionAllowedForMagicLink(string $action): bool
    {
        $this->checkOwnablePolicy();
        return in_array($action, $this->getMagicLinkAllowedActions());
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
     * 
     * @param TModelClass $model
     */
    protected function canView(User $user, Model&Ownable $model): bool
    {
        return $this->checkMagicLinkUsedAndAllowed('view') && $model->isViewableBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    protected function canCreate(User $user): bool
    {
        $modelClass = $this->getModelClass();
        return $this->checkMagicLinkUsedAndAllowed('create') && $modelClass::canCreateThis($user, request()->all());
    }

    /**
     * Determine whether the user can update the model.
     * 
     * @param TModelClass $model
     */
    protected function canUpdate(User $user, Model&Ownable $model): bool
    {
        $modelClass = $this->getModelClass();
        $mergedAttributes = array_merge($model->getAttributes(), request()->all());
        
        return $this->checkMagicLinkUsedAndAllowed('update') && $model->isOwnedBy($user) && $modelClass::canUpdateThis($user, $mergedAttributes);
    }

    /**
     * Determine whether the user can delete the model.
     * 
     * @param TModelClass $model
     */
    protected function canDelete(User $user, Model&Ownable $model): bool
    {   
        return $this->checkMagicLinkUsedAndAllowed('delete') && $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     * 
     * @param TModelClass $model
     */
    protected function canRestore(User $user, Model&Ownable $model): bool
    {
        return $this->checkMagicLinkUsedAndAllowed('restore') && $model->isOwnedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     * 
     * @param TModelClass $model
     */
    protected function canForceDelete(User $user, Model&Ownable $model): bool
    {
        return $this->checkMagicLinkUsedAndAllowed('forceDelete') && $model->isOwnedBy($user);
    }
} 