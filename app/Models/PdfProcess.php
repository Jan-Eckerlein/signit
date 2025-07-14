<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\PdfProcessStatus;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfProcess extends Model implements Lockable, Ownable
{
    use ProtectsLockedModels;

    protected $fillable = [
        'document_id',
    ];

    protected $casts = [
        'status' => PdfProcessStatus::class,
    ];

    // ---------------------------- RELATIONS ----------------------------

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    // ---------------------------- LOCKING -----------------------------

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('status') === PdfProcessStatus::PDF_SIGNED;
    }

    // ---------------------------- OWNERSHIP ---------------------------

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->document->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        return $query->where('document.owner_user_id', $user->id);
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        return $query->where('document.owner_user_id', $user->id);
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        $document = Document::find($attributes['document_id']);
        return $document && $document->isOwnedBy($user);
    }
}
