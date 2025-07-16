<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Contracts\OwnableBuilder;

/**
 * @template TModelClass of \App\Models\DocumentFieldValue
 * @extends BaseBuilder<TModelClass>
 */
class DocumentFieldValueBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentField.documentSigner.user', function (Builder $query) use ($user) {
            $this
                ->getBuilder($query, UserBuilder::class)
                ->whereId($user->id);
        });
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->ownedBy($user);
        return $this;
    }

    /** @return $this */
    public function completed(): self
    {
        $this->where(function ($q) {
            $q->whereNotNull('value_signature_sign_id')
                ->orWhereNotNull('value_initials')
                ->orWhereNotNull('value_text')
                ->orWhereNotNull('value_checkbox')
                ->orWhereNotNull('value_date');
        });
        return $this;
    }

    /** @return $this */
    public function incomplete(): self
    {
        $this->whereNull('value_signature_sign_id')
            ->whereNull('value_initials')
            ->whereNull('value_text')
            ->whereNull('value_checkbox')
            ->whereNull('value_date');
        return $this;
    }
} 