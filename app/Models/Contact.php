<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'name',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($contact) {
            if (!$contact->user_id && Auth::check()) {
                $contact->user_id = Auth::id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 