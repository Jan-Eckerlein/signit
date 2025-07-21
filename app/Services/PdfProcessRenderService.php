<?php

namespace App\Services;

use App\Models\PdfProcessPage;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class PdfProcessRenderService
{
    /**
     * Render fields onto a PDF process page and store the processed PDF.
     *
     * @param int $pdfProcessPageId
     * @param int[] $documentFieldIds
     * @return string $processedPath
     */
    public function renderFieldsOnPage(int $pdfProcessPageId, array $documentFieldIds): string
    {
        // Load the PdfProcessPage
        $processPage = PdfProcessPage::findOrFail($pdfProcessPageId);
        $originalPath = $processPage->pdf_original_path;
        $originalFullPath = Storage::path($originalPath);

        // Load the fields and their values
        $fields = DocumentField::with('value')->whereIn('id', $documentFieldIds)->get();

        // Prepare FPDI
        $pdf = new Fpdi();
        $pdf->setSourceFile($originalFullPath);
        $pdf->AddPage();
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx);

        // Loop over fields and render them (placeholder logic)
        foreach ($fields as $field) {
            $value = $field->value;
            // TODO: Render field based on type and value
            // Example: $pdf->Text($field->x, $field->y, $value?->value_text ?? '');
        }

        // Store the processed PDF
        $processedPath = 'pdf_process/processed/' . uniqid() . '_processed.pdf';
        Storage::put($processedPath, $pdf->Output('S'));

        // Update the process page
        $processPage->pdf_processed_path = $processedPath;
        $processPage->is_up_to_date = true;
        $processPage->save();

        return $processedPath;
    }
} 