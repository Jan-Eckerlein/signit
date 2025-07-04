<?php

namespace App\Exceptions;

use Exception;

class LockedModelException extends Exception
{
    public function __construct(string $modelClass)
    {
        parent::__construct("{$modelClass} is locked and cannot be modified.");
    }
} 