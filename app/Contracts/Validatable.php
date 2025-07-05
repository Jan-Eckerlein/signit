<?php

namespace App\Contracts;

interface Validatable
{
    public function validateModification(string $method, array $options): bool;
} 