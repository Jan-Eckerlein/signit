<?php

namespace App\Services;

use App\Models\DocumentField;
use App\Models\PdfProcessPage;
use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentFieldRenderDirectorService
{
    /**
     * Get field IDs grouped by PDF process page for completed signers of a document
     *
     * @param int $documentId
     * @return array<int, int[]> Array keyed by pdf_process_page_id containing arrays of document_field_ids
     * @throws \InvalidArgumentException
     */
    public function getCompletedFieldIdsGroupedByPdfProcessPage(int $documentId): array
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
     * Mock function to demonstrate dispatching jobs for each process page.
     *
     * @param array<int, int[]> $groupedFields
     * @return void
     */
    public function dispatchRenderJobs(array $groupedFields): void
    {
        foreach ($groupedFields as $pdfProcessPageId => $fieldIds) {
            // Dispatch job to render this page with these field IDs
            // RenderPdfProcessPageJob::dispatch($pdfProcessPageId, $fieldIds);
        }
    }
} 