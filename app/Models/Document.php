<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentStatus;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Document extends Model implements Lockable, Ownable, Validatable
{
    use HasFactory, ProtectsLockedModels, ValidatesModelModifications;

    protected $fillable = [
        'title',
        'owner_user_id',
        'description',
    ];

    protected $guarded = ['status'];

    protected $casts = [
        'status' => DocumentStatus::class,
    ];

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        if (!$this->isDirty('status')) return true;

        $from = $this->getOriginal('status');
        $to = $this->status;

        $validTransitions = [
            DocumentStatus::DRAFT => [DocumentStatus::OPEN],
            DocumentStatus::OPEN => [DocumentStatus::DRAFT, DocumentStatus::IN_PROGRESS, DocumentStatus::COMPLETED],
            DocumentStatus::IN_PROGRESS => [DocumentStatus::COMPLETED],
            DocumentStatus::TEMPLATE => [], // nie änderbar
        ];

        return (isset($validTransitions[$from]) && in_array($to, $validTransitions[$from], strict: true));
    }

    public function ownerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function documentSigners(): HasMany
    {
        return $this->hasMany(DocumentSigner::class);
    }

    public function documentLogs(): HasMany
    {
        return $this->hasMany(DocumentLog::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return $this->owner_user_id === ($user ? $user->id : Auth::id());
    }

    public function isViewableBy(User | null $user = null): bool
    {
        return $this->isOwnedBy($user) || $this->documentSigners()->where('user_id', $user ? $user->id : Auth::id())->exists();
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where('owner_user_id', $user->id);
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->where(function (Builder $query) use ($user) {
            $query
                // EIGENE Dokumente immer
                ->where('owner_user_id', $user->id)
                
                // FREMDE nur wenn sie "offen", "in progress" oder "completed" sind
                ->orWhere(function (Builder $query) use ($user) {
                    $query->whereIn('status', [
                            DocumentStatus::OPEN,
                            DocumentStatus::IN_PROGRESS,
                            DocumentStatus::COMPLETED,
                        ])
                        ->whereHas('documentSigners', function (Builder $q) use ($user) {
                            $q->where('user_id', $user->id);
                        });
                });
        });
    }
} 