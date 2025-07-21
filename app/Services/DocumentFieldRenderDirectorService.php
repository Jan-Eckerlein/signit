<?php

namespace App\Services;

use App\Models\DocumentField;
use App\Models\PdfProcessPage;
use App\Models\Document;
use Illuminate\Support\Collection;

class DocumentFieldRenderDirectorService
{
    /**
     * Given an array of DocumentField models, verify all belong to the same Document,
     * fetch the related PdfProcessPages via DocumentPage, and return an array grouped by pdf_process_page_id => [DocumentField].
     *
     * @param DocumentField[]|Collection<int, DocumentField> $fields
     * @return array<int, DocumentField[]>
     * @throws \InvalidArgumentException
     */
    public function groupFieldsByPdfProcessPage(array|Collection $fields): array
    {
        if (empty($fields)) {
            return [];
        }

        // Get the document for the first field
        /** @var DocumentField $firstField */
        $firstField = $fields[0];
        $document = $firstField->documentPage->document;
        if (!$document) {
            throw new \InvalidArgumentException('DocumentField is not attached to a Document.');
        }

        // Verify all fields belong to the same document
        foreach ($fields as $field) {
            if ($field->documentPage->document_id !== $document->id) {
                throw new \InvalidArgumentException('All DocumentFields must belong to the same Document. ' . $field->documentPage->document_id . ' ' . $document->id);
            }
        }

        // Get the PdfProcess for the document
        $pdfProcess = $document->pdfProcess;
        if (!$pdfProcess) {
            throw new \InvalidArgumentException('Document has no PdfProcess.');
        }

        // Map DocumentPage id to PdfProcessPage id
        $pageMap = $pdfProcess->pages->pluck('id', 'document_page_id'); // [document_page_id => pdf_process_page_id]

        // Group fields by pdf_process_page_id
        $result = [];
        foreach ($fields as $field) {
            $documentPageId = $field->document_page_id;
            $pdfProcessPageId = $pageMap[$documentPageId] ?? null;
            if ($pdfProcessPageId === null) {
                // Optionally skip or throw
                continue;
            }
            $result[$pdfProcessPageId][] = $field;
        }
        return $result;
    }

    /**
     * Mock function to demonstrate dispatching jobs for each process page.
     *
     * @param array<int, DocumentField[]> $groupedFields
     * @return void
     */
    public function dispatchRenderJobs(array $groupedFields): void
    {
        foreach ($groupedFields as $pdfProcessPageId => $fields) {
            // Mock: dispatch(new RenderPdfProcessPageJob($pdfProcessPageId, $fields));
            // For now, just log or print
            // logger()->info("Dispatching render job for page $pdfProcessPageId with " . count($fields) . " fields");
        }
    }
} 