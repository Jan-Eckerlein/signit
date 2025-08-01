<?php

namespace App\Models;

use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use App\Builders\SignBuilder;
use App\Contracts\Validatable;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Support\Facades\Storage;

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property string $image_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $archived_at
 * @property \Carbon\Carbon|null $updated_at
 */
class Sign extends Model implements Validatable, Ownable
{
    /** @use HasFactory<\Database\Factories\SignFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\SignBuilder> */
    use HasBuilder;
    use ValidatesModelModifications;

    protected static string $builder = SignBuilder::class;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'archived_at',
    ];

    protected static function booted()
    {
        static::deleted(function (Sign $sign) {
            if ($sign->image_path) {
                Storage::delete($sign->image_path);
            }
        });
    }

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

    // ---------------------------- Validation ----------------------------

    /** @return bool */
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        if ($this->isBeingUsed()) {
            $dirty = $this->getDirty();
            if (count($dirty) === 1 && array_key_exists('archived_at', $dirty)) {
                return true;
            }
            return false;
        }

        return true;
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


    // ---------------------------- ARCHIVE ----------------------------

    /**
     * Check if this sign is being used by any document fields
     */
    public function isBeingUsed(): bool
    {
        return $this->documentFieldValues()->exists();
    }

    public function archive(): bool
    {
        return $this->update(['archived_at' => now()]);
    }

    public function unarchive(): bool
    {
        return $this->update(['archived_at' => null]);
    }
} 