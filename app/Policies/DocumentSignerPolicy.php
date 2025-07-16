<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class DocumentSignerPolicy extends ComposablePolicy implements OwnablePolicy
{
	/** @use HandlesOwnable<\App\Models\DocumentSigner> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return [];
    }
} 