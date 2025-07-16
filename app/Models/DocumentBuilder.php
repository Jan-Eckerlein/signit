<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Enums\DocumentStatus;
use App\Contracts\OwnableBuilder;

/** @extends Builder<Document> */
class DocumentBuilder extends Builder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->where('owner_user_id', $user->id);
		return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->where(function (Builder $query) use ($user) {
            $query
                ->where('owner_user_id', $user->id)
                ->orWhere(function (Builder $query) use ($user) {
                    $query->whereIn('status', [
                            DocumentStatus::OPEN,
                            DocumentStatus::IN_PROGRESS,
                            DocumentStatus::COMPLETED,
                        ])
                        ->whereHas('documentSigners', function (Builder $q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                });
        });
		return $this;
    }

    /** @return $this */
    public function withIncompleteSigners(): self
    {
        $this->whereHas('documentSigners', function (Builder $query) {
            $query->whereNull('signature_completed_at');
        });
        return $this;
    }
} 