<?php

namespace App\Builders;

use App\Builders\DocumentBuilder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use App\Contracts\OwnableBuilder;

/**
 * @extends BaseBuilder<\App\Models\DocumentSigner>
 */
class DocumentSignerBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
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
        $user = $user ?? Auth::user();
        $this->whereHas('document', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, DocumentBuilder::class)
                ->viewableBy($user);
        });
        return $this;
    }
} 