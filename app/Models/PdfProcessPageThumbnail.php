<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ThumbnailSizes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pdf_process_page_id
 * @property string $path
 * @property ThumbnailSizes $size
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PdfProcessPageThumbnail extends Model
{
    protected $fillable = [
        'pdf_process_page_id',
        'path',
        'size',
    ];

    protected $casts = [
        'size' => ThumbnailSizes::class,
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<PdfProcessPage, $this> */
    public function pdfProcessPage(): BelongsTo
    {
        return $this->belongsTo(PdfProcessPage::class);
    }
}
