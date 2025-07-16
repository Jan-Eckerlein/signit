<?php

namespace App\Contracts;

use App\Enums\BaseModelEvent;

interface Validatable
{
    /**
     * @param array<string, mixed> $options
     */
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool;
} 