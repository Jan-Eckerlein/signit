<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    use HasFactory, ProtectsLockedModels;

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

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function documentFields(): HasMany
    {
        return $this->hasMany(DocumentField::class);
    }

    // ---------------------------- OWNERSHIP ----------------------------

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->document->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->document->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        $document = Document::find($attributes['document_id']);
        return $document->isOwnedBy($user);
    }
}
