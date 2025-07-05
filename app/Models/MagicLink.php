<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class MagicLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token', // Hide the hashed token from JSON/array output
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Check if the magic link is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the magic link is valid (not expired)
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Set the token (automatically hash it)
     */
    public function setTokenAttribute(string $value): void
    {
        $this->attributes['token'] = Hash::make($value);
    }

    /**
     * Check if the provided token matches this magic link
     */
    public function checkToken(string $token): bool
    {
        return Hash::check($token, $this->attributes['token']);
    }
} 