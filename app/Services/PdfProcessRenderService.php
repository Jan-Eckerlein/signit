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
            $x = $field->x;
            $y = $field->y;
            $width = $field->width;
            $height = $field->height;

            switch ($field->type->value) {
                case 'text':
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($width, $height, $value->value_text ?? '', 0, 0, 'L');
                    break;
                case 'checkbox':
                    $pdf->SetXY($x, $y);
                    $pdf->Rect($x, $y, $width, $height);
                    if ($value?->value_checkbox) {
                        $pdf->SetFont('Arial', 'B', 14);
                        $pdf->Text($x + 1, $y + $height - 1, 'YES');
                    } else {
                        $pdf->SetFont('Arial', 'B', 14);
                        $pdf->Text($x + 1, $y + $height - 1, 'NO');
                    }
                    break;
                case 'date':
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($width, $height, $value?->value_date ? $value->value_date->format('Y-m-d') : '', 0, 0, 'L');
                    break;
                case 'initials':
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->SetXY($x, $y);
                    $pdf->Cell($width, $height, $value->value_initials ?? '', 1, 0, 'C');
                    break;
                case 'signature':
                    if ($value?->signatureSign && $value->signatureSign->image_path) {
                        $pdf->Image(Storage::path($value->signatureSign->image_path), $x, $y, $width, $height);
                    } else {
                        $pdf->SetFont('Arial', 'I', 10);
                        $pdf->SetXY($x, $y);
                        $pdf->Cell($width, $height, '[Signature]', 1, 0, 'C');
                    }
                    break;
            }
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