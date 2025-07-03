<?php

namespace App\Models;

use App\Enums\DocumentFieldType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SignerDocumentField extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_signer_id',
        'page',
        'x',
        'y',
        'width',
        'height',
        'type',
        'label',
        'description',
        'required',
        'value_signature_sign_id',
        'value_initials',
        'value_text',
        'value_checkbox',
        'value_date',
    ];

    protected $casts = [
        'type' => DocumentFieldType::class,
        'required' => 'boolean',
        'value_checkbox' => 'boolean',
        'value_date' => 'date',
    ];

    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    public function signatureSign(): BelongsTo
    {
        return $this->belongsTo(Sign::class, 'value_signature_sign_id');
    }
} 