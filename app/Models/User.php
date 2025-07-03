<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'owner_user_id');
    }

    public function ownContacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'own_user_id');
    }

    
    public function isContactOf(): HasMany
    {
        return $this->hasMany(Contact::class, 'knows_user_id');
    }
    
    public function knowsUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'own_user_id', 'knows_user_id');
    }

    public function knowsAnonymousUsers(): BelongsToMany
    {
        return $this->belongsToMany(AnonymousUser::class, 'contacts', 'own_user_id', 'knows_anonymous_users_id');
    }

    public function signs(): HasMany
    {
        return $this->hasMany(Sign::class);
    }
}
