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
        'template_id',
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
            // Draft documents can be opened
            DocumentStatus::DRAFT => [DocumentStatus::OPEN],
            // Open documents can be send back to draft or marked as in progress
            DocumentStatus::OPEN => [DocumentStatus::DRAFT, DocumentStatus::IN_PROGRESS],
            // In progress documents can be completed
            DocumentStatus::IN_PROGRESS => [DocumentStatus::COMPLETED],
            // Template documents cannot change the status
            DocumentStatus::TEMPLATE => [],
            //! Completed documents are not editable at all
            DocumentStatus::COMPLETED => [],
        ];

        // If the document is new, only allow 'template' or 'draft' status
        if (!$this->exists && !in_array($this->status, [DocumentStatus::TEMPLATE, DocumentStatus::DRAFT], strict: true)) {
            throw new \Exception('New documents must be created as template or draft');
            return false;
        }

        // Check if transition is valid
        if (!isset($validTransitions[$from]) || !in_array($to, $validTransitions[$from], strict: true)) {
            throw new \Exception('Invalid status transition from ' . $from . ' to ' . $to);
            return false;
        }

        // Additional validation for IN_PROGRESS transition
        if ($to === DocumentStatus::IN_PROGRESS) {
            return $this->validateInProgressTransition();
        }

        return true;
    }

    private function validateInProgressTransition(): bool
    {
        // Check if document has signers
        if ($this->documentSigners()->count() === 0) {
            throw new \Exception('Document #' . $this->id . ' has no signers');
            return false;
        }

        // Check if all signers are bound to a user
        $unboundSigners = $this->documentSigners()->whereNull('user_id')->count();
        if ($unboundSigners > 0) {
            $signers = $this->documentSigners()->whereNull('user_id')->get();   
            $signerNames = $signers->map(function ($signer) {
                return $signer->name;
            })->implode(', ');
            throw new \Exception('There are ' . $unboundSigners . ' unbound signers (no user assigned) in this document. Please assign a user to the signers: ' . $signerNames);
            return false;
        }

        // Check if all signers have at least one field
        $signersWithoutFields = $this->documentSigners()
            ->whereDoesntHave('signerDocumentFields')
            ->count();
        
        if ($signersWithoutFields > 0) {
            throw new \Exception('Signer #' . $signersWithoutFields->id . ' has no fields');
            return false;
        }

        // Check if all fields are bound to signers (no unbound fields)
        $unboundFields = $this->signerDocumentFields()
            ->whereNull('document_signer_id')
            ->count();
        
        if ($unboundFields > 0) {
            throw new \Exception('Unbound fields: ' . $unboundFields);
            return false;
        }

        return true;
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

    public function signerDocumentFields(): HasMany
    {
        return $this->hasMany(SignerDocumentField::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
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

    public static function canCreateThis(User $user, array $attributes): bool
    {
        // Users can only create documents for themselves
        return $attributes['owner_user_id'] === $user->id;
    }

    public function scopeWithIncompleteSigners(Builder $query): Builder
    {
        return $query->whereHas('documentSigners', function ($query) {
            $query->whereNull('signature_completed_at');
        });
    }

    public function areAllSignersCompleted(): bool
    {
        return $this->documentSigners()
            ->whereNull('signature_completed_at')
            ->doesntExist();
    }

    public function getProgress(): array
    {
        return [
            'total_signers' => $this->documentSigners()->count(),
            'completed_signers' => $this->documentSigners()
                ->whereNotNull('signature_completed_at')
                ->count(),
            'signers_progress' => $this->documentSigners()
                ->with(['user', 'signerDocumentFields.value'])
                ->get()
                ->map(function ($signer) {
                    return [
                        'id' => $signer->id,
                        'user_name' => $signer->user->name,
                        'completed_fields' => $signer->getCompletedFieldsCount(),
                        'total_fields' => $signer->getTotalFieldsCount(),
                        'is_completed' => $signer->isSignatureCompleted(),
                        'completed_at' => $signer->signature_completed_at,
                    ];
                }),
        ];
    }
} 