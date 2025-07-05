<?php

namespace App\Traits;

use App\Contracts\Validatable;

trait ValidatesModelModifications
{
    protected bool $bypassValidateModification = false;

    protected static function bootValidatesModelModifications()
    {
        static::saving(function ($model) {
            $model->maybeValidateModification('save', []);
        });

        static::updating(function ($model) {
            $model->maybeValidateModification('update', []);
        });
    }

    protected function maybeValidateModification(string $method, array $options): bool
    {
        if ($this->bypassValidateModification) {
            return true;
        }

        if (!$this instanceof Validatable) {
            throw new \LogicException(static::class . ' must implement Validatable interface when using ValidatesModelModifications trait.');
        }

        return $this->validateModification($method, $options);
    }
} 