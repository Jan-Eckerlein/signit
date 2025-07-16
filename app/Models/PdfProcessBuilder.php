<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** @extends Builder<PdfProcess> */
class PdfProcessBuilder extends Builder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $this->where('document.owner_user_id', $user->id);
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $this->where('document.owner_user_id', $user->id);
        return $this;
    }
} 