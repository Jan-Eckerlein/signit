<?php

namespace App\Policies;

use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class SignerDocumentFieldPolicy extends ComposablePolicy
{
    use HandlesOwnable;
} 