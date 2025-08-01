<?php

namespace App\Services;

use App\Jobs\RenderFieldsOnPageJob;
use App\Models\DocumentField;
use App\Models\Document;
use App\Models\PdfProcessPage;

class DocumentFieldRenderDirectorService
{

    public static function directRender(int $documentId): void
    {
        $fieldIdGroups = self::getCompletedFieldIdsGroupedByPdfProcessPage($documentId);
        self::markPdfProcessPagesAsNonUpToDate(array_keys($fieldIdGroups));
        self::dispatchRenderJobs($fieldIdGroups);
    }

    public static function directRenderForSigner(int $signerId): void
    {
        $fieldIdGroups = self::getCompletedFieldIdsGroupedByPdfProcessPageForSigner($signerId);
        self::markPdfProcessPagesAsNonUpToDate(array_keys($fieldIdGroups));
        self::dispatchRenderJobs($fieldIdGroups);
    }

    /**
     * Get field IDs grouped by PDF process page for completed signers of a document
     *
     * @param int $documentId
     * @return array<int, int[]> Array keyed by pdf_process_page_id containing arrays of document_field_ids
     * @throws \InvalidArgumentException
     */
    public static function getCompletedFieldIdsGroupedByPdfProcessPage(int $documentId): array
    {
        // Validate that the document exists and has a PDF process
        $document = Document::with('pdfProcess')->find($documentId);
        if (!$document) {
            throw new \InvalidArgumentException('Document not found.');
        }

        if (!$document->pdfProcess) {
            throw new \InvalidArgumentException('Document has no PdfProcess.');
        }

        // Use the builder to get grouped field IDs efficiently
        return DocumentField::query()->getCompletedFieldIdsGroupedByPdfProcessPage($documentId);
    }

    /**
     * Get ALL completed signers' field IDs for PDF process pages affected by a specific signer
     * This is used when a signer completes their signature - you want to re-render entire pages
     * with all completed signers' fields, not just the completing signer's fields.
     *
     * @param int $signerId
     * @return array<int, int[]> Array keyed by pdf_process_page_id containing arrays of document_field_ids
     * @throws \InvalidArgumentException
     */
    public function getCompletedFieldIdsForPagesAffectedBySigner(int $signerId): array
    {
        // Use the builder to get all completed fields for pages affected by this signer
        return DocumentField::query()->getCompletedFieldIdsForPagesAffectedBySigner($signerId);
    }

    /**
     * Get field IDs grouped by PDF process page for completed signers of a document
     *
     * @param int $signerId
     * @return array<int, int[]> Array keyed by pdf_process_page_id containing arrays of document_field_ids
     * @throws \InvalidArgumentException
     */
    public static function getCompletedFieldIdsGroupedByPdfProcessPageForSigner(int $signerId): array
    {
        return DocumentField::query()->getCompletedFieldIdsForPagesAffectedBySigner($signerId);
    }

    /**
     * Mock function to demonstrate dispatching jobs for each process page.
     *
     * @param array<int, int[]> $groupedFields
     * @return void
     */
    public static function dispatchRenderJobs(array $groupedFields): void
    {
        foreach ($groupedFields as $pdfProcessPageId => $fieldIds) {
            RenderFieldsOnPageJob::dispatch($pdfProcessPageId, $fieldIds);
        }
    }

    /**
     * Mark PDF process pages as non-up-to-date
     *
     * @param array<int> $pdfProcessPageIds
     * @return void
     */
    public static function markPdfProcessPagesAsNonUpToDate(array $pdfProcessPageIds): void
    {
        PdfProcessPage::whereIn('id', $pdfProcessPageIds)->update(['is_up_to_date' => false]);
    }
} 