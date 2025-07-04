<?php

namespace App\Traits;

use App\Contracts\Lockable;
use App\Enums\DeletionStatus;
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

    public function delete(): DeletionStatus
    {
        $allowedSoftDeletes = $this->getAllowedSoftDeletes();
        if (!$allowedSoftDeletes) {
            $this->maybePreventModification();
        }

        $deleted = parent::delete();
        if ($deleted) {
            return $allowedSoftDeletes ? DeletionStatus::SOFT_DELETED : DeletionStatus::PERMANENTLY_DELETED;
        }
        return DeletionStatus::NOOP;
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

    protected function getAllowedSoftDeletes(): bool
    {
        if (!property_exists($this, 'allowSoftDeletes') || !$this->allowSoftDeletes) return false;
        if (!in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive($this))) {
            throw new \LogicException(static::class . ' must use SoftDeletes trait to allow soft deletes.');
        }
        return true;
    }
}