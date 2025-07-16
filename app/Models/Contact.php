<?php

namespace App\Models;

use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
    
/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $user_id
 * @property string $email
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Contact extends Model implements Ownable
{
    /** @use HasBuilder<\App\Builders\ContactBuilder> */
    use HasBuilder;

    protected $fillable = [
        'user_id',
        'email',
        'name',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---------------------------- OWNABLE ----------------------------

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->user_id === ($user ? $user->id : Auth::id());
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create contacts for themselves
        return $attributes['user_id'] === $user->id;
    }
} 