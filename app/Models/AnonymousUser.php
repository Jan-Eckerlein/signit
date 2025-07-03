<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnonymousUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
    ];

    public function isContactOf(): HasMany
    {
        return $this->hasMany(Contact::class, 'knows_anonymous_users_id');
    }

    public function signs(): HasMany
    {
        return $this->hasMany(Sign::class);
    }
} 