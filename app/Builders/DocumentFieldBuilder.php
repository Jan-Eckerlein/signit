<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\DocumentField;
use App\Builders\BaseBuilder;
use App\Builders\DocumentSignerBuilder;
use App\Contracts\OwnableBuilder;
use App\Models\User;
use Illuminate\Support\Facades\DB;


/**
 * @template TModelClass of DocumentField
 * @extends BaseBuilder<TModelClass>
 */
class DocumentFieldBuilder extends BaseBuilder implements OwnableBuilder
{
    /** @return $this */
    public function ownedBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
			$this
				->getBuilder($query, DocumentBuilder::class)
				->ownedBy($user);
        });
        return $this;
    }

    /** @return $this */
    public function viewableBy(User | null $user = null): self
    {
        $user = $user ?? Auth::user();
        $this->whereHas('documentSigner.document', function (Builder $query) use ($user) {
			$this
				->getBuilder($query, DocumentBuilder::class)
				->viewableBy($user);
    });
        return $this;
    }

    /** @return $this */
    public function completed(): self
    {
        $this->whereHas('value', function (Builder $query) {
            $this->getBuilder($query, DocumentFieldValueBuilder::class)
                ->completed();
        });
        return $this;
    }

    /** @return $this */
    public function incomplete(): self
    {
        $this->whereHas('value', function (Builder $query) {
            $this->getBuilder($query, DocumentFieldValueBuilder::class)
                ->incomplete();
        });
        return $this;
    }

    /**
     * Join with pdf_process_pages to get the pdf_process_page_id
     * @return $this
     */
    public function withPdfProcessPageId(): self
    {
        $this->join('document_pages', 'document_fields.document_page_id', '=', 'document_pages.id')
             ->join('documents', 'document_pages.document_id', '=', 'documents.id')
             ->join('pdf_processes', 'documents.id', '=', 'pdf_processes.document_id')
             ->join('pdf_process_pages', 'document_pages.id', '=', 'pdf_process_pages.document_page_id')
             ->select('document_fields.*', 'pdf_process_pages.id as pdf_process_page_id');
        return $this;
    }

    /**
     * Filter fields belonging to completed signers only
     * @return $this
     */
    public function forCompletedSigners(): self
    {
        $this->whereHas('documentSigner', function (Builder $query) {
            $this->getBuilder($query, DocumentSignerBuilder::class)
                ->completed();
        });
        return $this;
    }

    /**
     * Filter fields for a specific document
     * @return $this
     */
    public function forDocument(int $documentId): self
    {
        $this->whereHas('documentPage', function (Builder $query) use ($documentId) {
            $query->where('document_id', $documentId);
        });
        return $this;
    }

    /**
     * Filter fields for a specific signer
     * @return $this
     */
    public function forSigner(int $signerId): self
    {
        $this->where('document_signer_id', $signerId);
        return $this;
    }

    /**
     * Get field IDs grouped by PDF process page for completed signers of a document
     * @param int $documentId
     * @return array<int, int[]>
     */
    public function getCompletedFieldIdsGroupedByPdfProcessPage(int $documentId): array
    {
        return $this->forDocument($documentId)
                   ->forCompletedSigners()
                   ->withPdfProcessPageId()
                   ->get(['document_fields.id', 'pdf_process_pages.id as pdf_process_page_id'])
                   ->groupBy('pdf_process_page_id')
                   ->map(function ($fields) {
                       return $fields->pluck('id')->toArray();
                   })
                   ->toArray();
    }

    /**
     * Get ALL completed signers' field IDs for PDF process pages that are affected by a specific signer
     * @param int $signerId
     * @return array<int, int[]>
     */
    public function getCompletedFieldIdsForPagesAffectedBySigner(int $signerId): array
    {
        // Single query: Get all completed signers' fields for pages where the specific signer has fields AND has completed
        return $this->forCompletedSigners()
            ->withPdfProcessPageId()
            ->whereExists(function ($query) use ($signerId) {
                $query->select(DB::raw(1))
                    ->from('document_fields as df2')
                    ->join('document_signers as ds', 'df2.document_signer_id', '=', 'ds.id')
                    ->whereColumn('df2.document_page_id', 'document_fields.document_page_id')
                    ->where('df2.document_signer_id', $signerId)
                    ->whereNotNull('ds.signature_completed_at');
            })
            ->get(['document_fields.id', 'pdf_process_pages.id as pdf_process_page_id'])
            ->groupBy('pdf_process_page_id')
            ->map(function ($fields) {
                return $fields->pluck('id')->toArray();
            })
            ->toArray();
    }
} 