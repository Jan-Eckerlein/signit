<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Enums\BaseModelEvent;
use App\Enums\Icon;
use App\Traits\ProtectsLockedModels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Builders\DocumentLogBuilder;
use Illuminate\Database\Eloquent\HasBuilder;

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $contact_id
 * @property int $document_signer_id
 * @property int $document_id
 * @property string $ip
 * @property \Carbon\Carbon $date
 * @property Icon $icon
 * @property string $text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class DocumentLog extends Model implements Ownable, Lockable
{
    /** @use HasFactory<\Database\Factories\DocumentLogFactory> */
    use HasFactory;
    /** @use HasBuilder<\App\Builders\DocumentLogBuilder> */
    use HasBuilder;
    use ProtectsLockedModels;

    protected static string $builder = DocumentLogBuilder::class;

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

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->exists;
    }

    /** @return BelongsTo<DocumentSigner, $this> */
    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    /** @return BelongsTo<Document, $this> */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        return false;
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document->isViewableBy($user);
    }

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        // No one can create a document log only the system can
        return false;
    }
} 