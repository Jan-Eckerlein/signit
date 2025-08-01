<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class ContactPolicy extends ComposablePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\Contact> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return [];
    }
}
