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
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Auth;
use App\Builders\DocumentFieldBuilder;
use Illuminate\Database\Eloquent\HasBuilder;

// ---------------------------- PROPERTIES ----------------------------

/**
 * @implements Ownable<self>
 * @property int $id
 * @property int $document_page_id
 * @property int|null $document_signer_id
 * @property int $page
 * @property float $x
 * @property float $y
 * @property float $width
 * @property float $height
 * @property DocumentFieldType $type
 * @property string $label
 * @property string|null $description
 * @property bool $required
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon|null $updated_at
 */
class DocumentField extends Model implements Lockable, Ownable, Validatable
{
    /** @use HasBuilder<\App\Builders\DocumentFieldBuilder> */
    use HasBuilder;
    use ProtectsLockedModels, ValidatesModelModifications;

    protected static string $builder = DocumentFieldBuilder::class;

    protected $fillable = [
        'document_page_id',
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

    // ---------------------------- RELATIONS ----------------------------

    /** @return BelongsTo<DocumentSigner, $this> */
    public function documentSigner(): BelongsTo
    {
        return $this->belongsTo(DocumentSigner::class);
    }

    /** @return BelongsTo<DocumentPage, $this> */
    public function documentPage(): BelongsTo
    {
        return $this->belongsTo(DocumentPage::class);
    }

    /** @return HasOneThrough<Document, DocumentPage, $this> */
    public function document(): HasOneThrough
    {
        return $this->hasOneThrough(Document::class, DocumentPage::class);
    }

    /** @return HasOne<DocumentFieldValue, $this> */
    public function value(): HasOne
    {
        return $this->hasOne(DocumentFieldValue::class);
    }

    // ---------------------------- LOCKING ----------------------------

    /** @return bool */
    public function isLocked(BaseModelEvent | null $event = null): bool
    {
        return $this->document?->getOriginal('status') === DocumentStatus::COMPLETED;
    }

    // ---------------------------- VALIDATION ----------------------------

    /** @return bool */
    public function validateModification(BaseModelEvent | null $event = null, array $options = []): bool
    {
        // change only when its status is draft:
        $draftFields = [
            'document_page_id', 'document_signer_id', 'page', 'x', 'y', 'width', 'height', 'type', 'label', 'description', 'required'
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
            if (!$documentSigner || $documentSigner->document_id != $this->documentPage?->document_id) {
                throw new \Exception('Document signer must belong to the same document as the field.');
            }
        }

        return true;
    }

    // ---------------------------- OWNERSHIP ----------------------------

    /** @return bool */
    public function isOwnedBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document?->isOwnedBy($user);
    }

    /** @return bool */
    public function isViewableBy(User | null $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $this->document?->isViewableBy($user);
    }


    // ---------------------------- UTILITIES ----------------------------

    /** @return bool */
    public static function canCreateThis(User $user, array $attributes): bool
    {
        $documentPage = DocumentPage::with('document')->find($attributes['document_page_id']);
        return $documentPage && $documentPage->document->isOwnedBy($user);
    }
} 