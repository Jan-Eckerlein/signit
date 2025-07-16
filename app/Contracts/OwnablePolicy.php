<?php

namespace App\Contracts;

interface OwnablePolicy
{
    /**
     * Get the actions that are allowed for magic link users
     * @return array<int, 'viewAny' | 'view' | 'create' | 'update' | 'delete' | 'restore' | 'forceDelete'>
     */
    public function getMagicLinkAllowedActions(): array;
} 