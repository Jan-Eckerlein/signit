<?php

namespace App\Builders;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\OwnableBuilder;

/**
 * @template TModelClass of \App\Models\PdfProcess
 * @extends BaseBuilder<TModelClass>
 */
class PdfProcessBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->ownedBy($user);
        });
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->ownedBy($user);
        });
        return $this;
    }
} 