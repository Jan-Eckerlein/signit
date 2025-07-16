<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\SignBuilder;
use Illuminate\Database\Eloquent\HasBuilder;

/**
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Sign extends Model implements Lockable, Ownable
{
    use HasFactory, SoftDeletes, ProtectsLockedModels, HasBuilder;

    protected static string $builder = SignBuilder::class;

    protected $allowSoftDeletes = true;

    protected $fillable = [
        'user_id',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<DocumentFieldValue, $this> */
    public function documentFieldValues(): HasMany
    {
        return $this->hasMany(DocumentFieldValue::class, 'value_signature_sign_id');
    }

    // ---------------------------- LOCKING ----------------------------

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->documentFieldValues()->exists();
    }

    // ---------------------------- OWNERSHIP ----------------------------

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->user_id === $user->id;
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->isOwnedBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create signs for themselves
        return $attributes['user_id'] === $user->id;
    }
} 