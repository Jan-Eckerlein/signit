<?php

namespace App\Traits;

use App\Contracts\Lockable;
use App\Enums\BaseModelEvent;
use App\Exceptions\LockedModelException;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @phpstan-require-implements \App\Contracts\Lockable
 */
trait ProtectsLockedModels
{
    protected bool $bypassLockedProtection = false;

    public function allowBypass(): static
    {
        $this->bypassLockedProtection = true;
        return $this;
    }

    protected static function bootProtectsLockedModels(): void
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
        /** @phpstan-ignore-next-line */
        if (method_exists(static::class, 'forceDeleting')) {
            static::forceDeleting(function ($model) {
                $model->maybePreventModification(BaseModelEvent::FORCE_DELETING);
            });
        }
    }

    protected function maybePreventModification(BaseModelEvent | null $event = null): void
    {
        if ($this->bypassLockedProtection || !config('model-protection.locking.enabled', false)) {
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
        /** @phpstan-ignore-next-line */
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this))) {
            throw new \LogicException(static::class . ' must use SoftDeletes trait to allow soft deletes.');
        }
        /** @phpstan-ignore-next-line */
        return (property_exists($this, 'allowSoftDeletes') && $this->allowSoftDeletes);
    }
}