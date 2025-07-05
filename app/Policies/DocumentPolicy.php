<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Models\Document;
use App\Models\User;
use App\Policies\Composables\ComposablePolicy;
use App\Policies\Composables\HandlesOwnable;
use Illuminate\Auth\Access\Response;

/**
 * @extends OwnablePolicy<Document>
 */
class DocumentPolicy extends ComposablePolicy implements OwnablePolicy
{
	use HandlesOwnable;

	public function getMagicLinkAllowedActions(): array
	{
		return ['view', 'read'];
	}
}
