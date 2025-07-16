<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use App\Contracts\OwnableBuilder;
use App\Models\User;

/**
 * @template TModelClass of \App\Models\DocumentPage
 * @extends BaseBuilder<TModelClass>
 */
class DocumentPageBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        return $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->ownedBy($user);
        });
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        return $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->viewableBy($user);
        });
    }
} 