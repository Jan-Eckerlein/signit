<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentField;
use App\Builders\BaseBuilder;
use App\Contracts\OwnableBuilder;
use App\Models\User;


/**
 * @template TModelClass of DocumentField
 * @extends BaseBuilder<TModelClass>
 */
class DocumentFieldBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
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
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
			$this
				->getBuilder($query, DocumentBuilder::class)
				->viewableBy($user);
    });
        return $this;
    }

    /** @return $this */
    public function completed(): self
    {
        $this->whereHas('value', function (Builder $query) {
            $this->getBuilder($query, DocumentFieldValueBuilder::class)
                ->completed();
        });
        return $this;
    }

    /** @return $this */
    public function incomplete(): self
    {
        $this->whereHas('value', function (Builder $query) {
            $this->getBuilder($query, DocumentFieldValueBuilder::class)
                ->incomplete();
        });
        return $this;
    }
} 