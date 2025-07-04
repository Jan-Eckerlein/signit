<?php

namespace App\Contracts;

interface Lockable
{
    public function isLocked(): bool;

    public function validateModification(string $method, array $options): bool;
} 