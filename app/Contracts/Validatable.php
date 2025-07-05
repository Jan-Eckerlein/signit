<?php

namespace App\Contracts;

use App\Enums\BaseModelEvent;

interface Validatable
{
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool;
} 