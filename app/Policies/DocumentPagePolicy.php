<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\HandlesOwnable;
use App\Policies\Composables\ComposablePolicy;

class DocumentPagePolicy extends ComposablePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\DocumentPage> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return ['view'];
    }
}
