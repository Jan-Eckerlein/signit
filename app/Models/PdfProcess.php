<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\PdfProcessStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\PdfProcessBuilder;
use Illuminate\Database\Eloquent\HasBuilder;

// ---------------------------- PROPERTIES ----------------------------

/**
 * @property int $id
 * @property int $document_id
 * @property PdfProcessStatus $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */

class PdfProcess extends Model implements Lockable, Ownable
{
    use ProtectsLockedModels, HasBuilder;

    protected static string $builder = PdfProcessBuilder::class;

    protected $fillable = [
        'document_id',
    ];

    protected $casts = [
        'status' => PdfProcessStatus::class,
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    // ---------------------------- LOCKING ----------------------------

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('status') === PdfProcessStatus::PDF_SIGNED;
    }

    // ---------------------------- OWNERSHIP ----------------------------

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->document->isOwnedBy($user);
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    // ---------------------------- UTILITIES ----------------------------

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        $document = Document::find($attributes['document_id']);
        return $document && $document->isOwnedBy($user);
    }
}
