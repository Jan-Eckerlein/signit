<?php

namespace App\Policies;

use App\Models\SignerDocumentFieldValue;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SignerDocumentFieldValuePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, SignerDocumentFieldValue $signerDocumentFieldValue): bool
    {
        return $signerDocumentFieldValue->isViewableBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, SignerDocumentFieldValue $signerDocumentFieldValue): bool
    {
        return $signerDocumentFieldValue->isEditableBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, SignerDocumentFieldValue $signerDocumentFieldValue): bool
    {
        return $signerDocumentFieldValue->isOwnedBy($user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, SignerDocumentFieldValue $signerDocumentFieldValue): bool
    {
        return $signerDocumentFieldValue->isOwnedBy($user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, SignerDocumentFieldValue $signerDocumentFieldValue): bool
    {
        return $signerDocumentFieldValue->isOwnedBy($user);
    }
} 