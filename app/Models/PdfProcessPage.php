<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $pdf_process_id
 * @property int|null $document_page_id
 * @property float|null $tmp_order
 * @property string $pdf_original_path
 * @property string|null $pdf_processed_path
 * @property bool $is_up_to_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PdfProcessPage extends Model
{
    /** @use HasFactory<\Database\Factories\PdfProcessPageFactory> */
    use HasFactory;

    protected $fillable = [
        'pdf_process_id',
        'document_page_id',
        'pdf_original_path',
        'pdf_processed_path',
        'is_up_to_date',
        'tmp_order',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<PdfProcess, $this> */
    public function pdfProcess(): BelongsTo
    {
        return $this->belongsTo(PdfProcess::class);
    }

    /** @return BelongsTo<DocumentPage, $this> */
    public function documentPage(): BelongsTo
    {
        return $this->belongsTo(DocumentPage::class);
    }

    /** @return HasMany<PdfProcessPageThumbnail, $this> */
    public function thumbnails(): HasMany
    {
        return $this->hasMany(PdfProcessPageThumbnail::class);
    }
}
