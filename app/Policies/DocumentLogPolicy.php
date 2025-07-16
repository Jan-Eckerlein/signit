<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;

class DocumentLogPolicy extends ComposablePolicy implements OwnablePolicy
{
	/** @use HandlesOwnable<\App\Models\DocumentLog> */
	use HandlesOwnable;

	public function getMagicLinkAllowedActions(): array
	{
		return [];
	}
} 