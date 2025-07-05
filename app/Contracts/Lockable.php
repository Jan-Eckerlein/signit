<?php

namespace App\Contracts;

use App\Enums\BaseModelEvent;

interface Lockable
{
    public function isLocked(BaseModelEvent | null $event = null): bool;
} 