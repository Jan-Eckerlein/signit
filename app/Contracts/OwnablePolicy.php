<?php

namespace App\Contracts;

interface OwnablePolicy
{
    /**
     * Get the actions that are allowed for magic link users
     */
    public function getMagicLinkAllowedActions(): array;
} 