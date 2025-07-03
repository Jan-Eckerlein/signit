<?php

namespace App\Models;

use App\Enums\Icon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'document_id',
        'ip',
        'date',
        'icon',
        'text',
    ];

    protected $casts = [
        'icon' => Icon::class,
        'date' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
} 