<?php

namespace App\Models;

use App\Contracts\Lockable;
use App\Contracts\Ownable;
use App\Contracts\Validatable;
use App\Enums\BaseModelEvent;
use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Models\User;
use App\Traits\ProtectsLockedModels;
use App\Traits\ValidatesModelModifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class SignerDocumentField extends Model implements Lockable, Ownable, Validatable
{
    use HasFactory, ProtectsLockedModels, ValidatesModelModifications;

    protected $fillable = [
        'document_id',
        'document_signer_id',
        'page',
        'x',
        'y',
        'width',
        'height',
        'type',
        'label',
        'description',
        'required',
    ];

    protected $casts = [
        'type' => DocumentFieldType::class,
        'required' => 'boolean',
    ];

    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->document?->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        // change only when its status is draft:
        $draftFields = [
            'document_signer_id', 'page', 'x', 'y', 'width', 'height', 'type', 'label', 'description', 'required'
        ];
        foreach ($draftFields as $field) {
            if ($this->isDirty($field)) {
                $status = $this->document?->getOriginal('status');
                if ($status !== DocumentStatus::DRAFT) {
                    return false;
                }
            }
        }

        // Validate that document_signer_id belongs to the same document
        if ($this->isDirty('document_signer_id') && $this->document_signer_id) {
            $documentSigner = DocumentSigner::find($this->document_signer_id);
            if (!$documentSigner || $documentSigner->document_id != $this->document_id) {
                throw new \Exception('Document signer must belong to the same document as the field.');
            }
        }

        return true;
    }

    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function value(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SignerDocumentFieldValue::class);
    }

    public function isOwnedBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document?->isOwnedBy($user);
    }

    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document?->isViewableBy($user);
    }

    public function scopeOwnedBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->ownedBy($user);
        });
    }

    public function scopeViewableBy(Builder $query, User | null $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $query->whereHas('documentSigner.document', function (Builder $query) use ($user) {
            $query->viewableBy($user);
        });
    }

    public static function canCreateThis(User $user, array $attributes): bool
    {
        $document = Document::find($attributes['document_id']);
        return $document && $document->isOwnedBy($user);
    }
} 