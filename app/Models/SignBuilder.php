<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** @extends Builder<Sign> */
class SignBuilder extends Builder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->where('user_id', $user->id);
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $this->ownedBy($user);
        return $this;
    }
} 