<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface OwnableBuilder extends Builder
{
    public function ownedBy(User | null $user = null): self;
    public function viewableBy(User | null $user = null): self;
} 