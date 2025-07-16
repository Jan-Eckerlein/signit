<?php

namespace App\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Contracts\OwnableBuilder;

/**
 * @template TModelClass of \App\Models\Sign
 * @extends BaseBuilder<TModelClass>
 */
class ContactBuilder extends BaseBuilder implements OwnableBuilder
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