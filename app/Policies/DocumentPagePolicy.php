<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\HandlesOwnable;

class DocumentPagePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\DocumentPage> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return ['view'];
    }
}
