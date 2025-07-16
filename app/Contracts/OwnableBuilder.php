<?php

namespace App\Contracts;

use App\Models\User;

interface OwnableBuilder
{
    public function ownedBy(User | null $user = null): self;
    public function viewableBy(User | null $user = null): self;
} 