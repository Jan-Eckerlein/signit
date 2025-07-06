<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\Icon;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class DocumentLog extends Model implements Ownable, Lockable
{
    use HasFactory, ProtectsLockedModels;

    protected $fillable = [
        'contact_id',
        'document_signer_id',
        'document_id',
        'ip',
        'date',
        'icon',
        'text',
    ];

    protected $casts = [
        'icon' => Icon::class,
        'date' => 'datetime',
    ];

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->exists;
    }

    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        return false;
    }

    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        return $query->whereRaw('1 = 0');
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // No one can create a document log only the system can
        return false;
    }
} 