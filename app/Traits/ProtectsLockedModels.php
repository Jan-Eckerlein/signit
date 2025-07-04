<?php

namespace App\Traits;

use App\Contracts\Lockable;
use App\Exceptions\LockedModelException;

trait ProtectsLockedModels
{
    protected bool $bypassLockedProtection = false;
    protected bool $bypassValidateModification = false;

    public function allowBypass(): static
    {
        $this->bypassLockedProtection = true;
        return $this;
    }

    public function save(array $options = []): bool
    {
        $this->maybePreventModification();
        $this->maybeValidateModification('save', $options);
        return parent::save($options);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        $this->maybePreventModification();
        $this->maybeValidateModification('update', $options);
        return parent::update($attributes, $options);
    }

    public function delete(): bool|null
    {
        $this->maybePreventModification();
        return parent::delete();
    }

    public function forceDelete()
    {
        $this->maybePreventModification();
        return parent::forceDelete();
    }

    protected function maybeValidateModification(string $method, array $options): bool
    {
        if ($this->bypassValidateModification) {
            return true;
        }

        if (!$this instanceof Lockable) {
            throw new \LogicException(static::class . ' must implement Lockable interface when using ProtectsLockedModels trait.');
        }

        return $this->validateModification($method, $options);
    }

    protected function maybePreventModification(): void
    {
        if ($this->bypassLockedProtection) {
            return;
        }

        if (!$this instanceof Lockable) {
            throw new \LogicException(static::class . ' must implement Lockable interface when using ProtectsLockedModels trait.');
        }

        if ($this->isLocked()) {
            throw new LockedModelException(static::class);
        }
    }
}