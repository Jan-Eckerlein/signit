<?php

namespace App\Exceptions;

use Exception;

class ValidateModelModificationFailedException extends Exception
{
    public function __construct(string $modelClass, string $message)
    {
        parent::__construct("{$modelClass} modification validation failed: {$message}");
    }
} 