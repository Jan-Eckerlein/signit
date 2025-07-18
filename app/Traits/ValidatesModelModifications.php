<?php

namespace App\Traits;

use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;

trait ValidatesModelModifications
{
    protected bool $bypassValidateModification = false;

    protected static function bootValidatesModelModifications(): void
    {
        static::saving(function ($model) {
            $model->maybeValidateModification(BaseModelEvent::SAVING, []);
        });

        static::updating(function ($model) {
            $model->maybeValidateModification(BaseModelEvent::UPDATING, []);
        });
    }

    /**
     * @param BaseModelEvent $event
     * @param array<string, mixed> $options
     * @return bool
     */
    protected function maybeValidateModification(BaseModelEvent $event, array $options): bool
    {
        if ($this->bypassValidateModification || !config('model-protection.validation.enabled', false)) {
            return true;
        }

        if (!$this instanceof Validatable) {
            throw new \LogicException(static::class . ' must implement Validatable interface when using ValidatesModelModifications trait.');
        }

        return $this->validateModification($event, $options);
    }
} 