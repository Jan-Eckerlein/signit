<?php

namespace App\Models;

use App\Contracts\Ownable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $pdf_process_id
 * @property string $name
 * @property string $path
 * @property string $size
 * @property float $order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class PdfProcessUpload extends Model implements Ownable
{
    protected $fillable = [
        'pdf_process_id',
        'name',
        'path',
        'size',
        'order',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<PdfProcess, $this> */
    public function pdfProcess(): BelongsTo
    {
        return $this->belongsTo(PdfProcess::class);
    }

    // ---------------------------- OWNERSHIP ----------------------------

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->pdfProcess->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->pdfProcess->isViewableBy($user);
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        $pdfProcess = PdfProcess::find($attributes['pdf_process_id']);
        return $pdfProcess->isOwnedBy($user);
    }
}
