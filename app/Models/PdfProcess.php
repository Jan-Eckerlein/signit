<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Builders\PdfProcessBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\HasBuilder;
use Illuminate\Database\Eloquent\Relations\HasMany;

// ---------------------------- PROPERTIES ----------------------------

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $document_id
 * @property bool $is
 * @property string|null $pdf_final_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */

class PdfProcess extends Model implements Ownable, Lockable
{
    /** @use HasFactory<\Database\Factories\PdfProcessFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\PdfProcessBuilder> */
    use ProtectsLockedModels, HasBuilder;

    protected static string $builder = PdfProcessBuilder::class;

    protected $fillable = [
        'document_id',
        'pdf_final_path',
    ];

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return HasMany<PdfProcessPage, $this> */
    public function pages(): HasMany
    {
        return $this->hasMany(PdfProcessPage::class);
    }

    /** @return HasMany<PdfProcessUpload, $this> */
    public function uploads(): HasMany
    {
        return $this->hasMany(PdfProcessUpload::class);
    }

    // ---------------------------- LOCKING ----------------------------

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('pdf_final_path') !== null;
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
