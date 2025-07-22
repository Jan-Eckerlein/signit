<?php

namespace App\Policies;

use App\Contracts\OwnablePolicy;
use App\Enums\DocumentStatus;
use App\Models\DocumentSigner;
use App\Models\User;
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

    public function completeSignature(User $user, DocumentSigner $documentSigner): bool
    {
        return $documentSigner->document->isStatus(DocumentStatus::IN_PROGRESS, DocumentStatus::OPEN)
            && $documentSigner->user->id === $user->id;
    }
} 