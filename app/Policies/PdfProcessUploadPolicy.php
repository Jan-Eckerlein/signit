<?php

namespace App\Policies;


use App\Contracts\OwnablePolicy;
use App\Policies\Composables\HandlesOwnable;
use App\Policies\Composables\ComposablePolicy;

class PdfProcessUploadPolicy extends ComposablePolicy implements OwnablePolicy
{
    /** @use HandlesOwnable<\App\Models\PdfProcessUpload> */
    use HandlesOwnable;

    public function getMagicLinkAllowedActions(): array
    {
        return [];
    }
}
