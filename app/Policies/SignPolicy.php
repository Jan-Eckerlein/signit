<?php

namespace App\Policies;

use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class SignPolicy extends ComposablePolicy
{
    use HandlesOwnable;
} 