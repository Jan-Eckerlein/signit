<?php

namespace App\Contracts;

interface Lockable
{
    public function isLocked(): bool;
} 