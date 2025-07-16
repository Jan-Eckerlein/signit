<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class SignPolicy extends ComposablePolicy implements OwnablePolicy
{
	/** @use HandlesOwnable<\App\Models\Sign> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];
    }
} 