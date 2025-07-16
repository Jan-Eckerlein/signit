<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Builders\DocumentBuilder;
use App\Contracts\OwnableBuilder;

/**
 * @template TModelClass of \App\Models\DocumentLog
 * @extends BaseBuilder<TModelClass>
 */
class DocumentLogBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $this->whereRaw('1 = 0');
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->viewableBy($user);
        });
        return $this;
    }
} 