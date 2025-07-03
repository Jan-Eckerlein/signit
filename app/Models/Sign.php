<?php

namespace App\Models;

use App\Enums\DeletionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'anonymous_user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class);
    }

    public function signerDocumentFields(): HasMany
    {
        return $this->hasMany(SignerDocumentField::class, 'value_signature_sign_id');
    }

    /**
     * Check if this sign is being used by any document fields
     */
    public function isBeingUsed(): bool
    {
        return $this->signerDocumentFields()->exists();
    }

    /**
     * Override the delete method to implement custom logic
     * 
     * @throws \LogicException
     */
    public function delete(): DeletionStatus
    {
        if ($this->isBeingUsed()) {
            // If the sign is being used, only soft delete it
            if (parent::delete()) {
                return DeletionStatus::SOFT_DELETED;
            }
            return DeletionStatus::NOOP;
        } else {
            // If the sign is not being used, force delete it
            if (parent::forceDelete()) {
                return DeletionStatus::PERMANENTLY_DELETED;
            }
            return DeletionStatus::NOOP;
        }
    }

    /**
     * Force delete the sign (only if not being used)
     */
    public function forceDeleteIfNotUsed()
    {
        if (!$this->isBeingUsed()) {
            return parent::forceDelete();
        }
        
        throw new \Exception('Cannot force delete sign that is being used by document fields.');
    }
} 