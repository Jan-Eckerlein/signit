<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Document;
use App\Builders\DocumentPageBuilder;
use Illuminate\Database\Eloquent\HasBuilder;

/**
 * @property int $id
 * @property int $document_id
 * @property int $page_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class DocumentPage extends Model implements Lockable, Ownable
{
    /** @use HasFactory<\Database\Factories\DocumentPageFactory> */
    use HasFactory, ProtectsLockedModels, HasBuilder;

    protected static string $builder = DocumentPageBuilder::class;

    protected $fillable = [
        'document_id',
        'page_number',
    ];

    // ---------------------------- LOCKING ----------------------------

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return !$this->document->isStatus(DocumentStatus::TEMPLATE, DocumentStatus::DRAFT);
    }

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return HasMany<DocumentField, $this> */
    public function documentFields(): HasMany
    {
        return $this->hasMany(DocumentField::class);
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
        return $this->document->isViewableBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        $document = Document::find($attributes['document_id']);
        return $document->isOwnedBy($user);
    }
}
