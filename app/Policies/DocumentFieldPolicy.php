<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class DocumentFieldPolicy extends ComposablePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\DocumentField> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return ['viewAny', 'view'];
    }
} 