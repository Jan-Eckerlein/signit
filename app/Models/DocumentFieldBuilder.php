<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/** @extends Builder<DocumentField> */
class DocumentFieldBuilder extends Builder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
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