<?php

namespace App\Traits;

use App\Contracts\Lockable;
use App\Enums\BaseModelEvent;
use App\Exceptions\LockedModelException;

trait ProtectsLockedModels
{
    protected bool $bypassLockedProtection = false;

    public function allowBypass(): static
    {
        $this->bypassLockedProtection = true;
        return $this;
    }

    protected static function bootProtectsLockedModels()
    {
        static::saving(function ($model) {
            $model->maybePreventModification(BaseModelEvent::SAVING);
        });

        static::updating(function ($model) {
            $model->maybePreventModification(BaseModelEvent::UPDATING);
        });

        static::deleting(function ($model) {
            $allowedSoftDeletes = $model->getAllowedSoftDeletes();
            if (!$allowedSoftDeletes) {
                $model->maybePreventModification(BaseModelEvent::DELETING);
            }
        });

        // Only register forceDeleting event if the model uses SoftDeletes
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class))) {
            static::forceDeleting(function ($model) {
                $model->maybePreventModification(BaseModelEvent::FORCE_DELETING);
            });
        }
    }

    protected function maybePreventModification(BaseModelEvent | null $event = null): void
    {
        if ($this->bypassLockedProtection) {
            return;
        }

        if (!$this instanceof Lockable) {
            throw new \LogicException(static::class . ' must implement Lockable interface when using ProtectsLockedModels trait.');
        }

        if ($this->isLocked($event)) {
            throw new LockedModelException(static::class);
        }
    }

    protected function getAllowedSoftDeletes(): bool
    {
        if (!property_exists($this, 'allowSoftDeletes') || !$this->allowSoftDeletes) return false;
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this))) {
            throw new \LogicException(static::class . ' must use SoftDeletes trait to allow soft deletes.');
        }
        return true;
    }
}