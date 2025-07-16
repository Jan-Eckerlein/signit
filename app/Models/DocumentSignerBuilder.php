<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** @extends Builder<DocumentSigner> */
class DocumentSignerBuilder extends Builder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
        return $this;
    }
} 