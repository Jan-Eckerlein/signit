<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Models\PdfProcess;
use App\Models\User;
use App\Policies\Composables\HandlesOwnable;
use App\Policies\Composables\ComposablePolicy;
use Illuminate\Auth\Access\Response;

class PdfProcessPolicy extends ComposablePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\PdfProcess> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return [];
    }
}
