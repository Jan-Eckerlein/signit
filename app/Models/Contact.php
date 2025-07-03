<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'own_user_id',
        'knows_user_id',
        'knows_anonymous_users_id',
        'email',
        'name',
    ];

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'own_user_id');
    }

    public function knowsUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'knows_user_id');
    }

    public function knowsAnonymousUser(): BelongsTo
    {
        return $this->belongsTo(AnonymousUser::class, 'knows_anonymous_users_id');
    }

    public function documentSigners(): HasMany
    {
        return $this->hasMany(DocumentSigner::class);
    }

    public function documentLogs(): HasMany
    {
        return $this->hasMany(DocumentLog::class);
    }
} 