<?php

namespace App\Services;

use App\Models\PdfProcess;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class PdfProcessMergeService
{
    /**
     * Merge all PDF files from a PDF process's pages in correct order.
     *
     * @param PdfProcess $pdfProcess
     * @return string The path to the merged PDF file
     * @throws \Exception If merging fails
     */
    public function mergePdfPages(PdfProcess $pdfProcess): string
    {
        // Load all pages with their document pages, ordered by page number
        $processPages = $pdfProcess->pages()
            ->with('documentPage')
            ->whereHas('documentPage')
            ->get()
            ->sortBy(function ($processPage) {
                return $processPage->documentPage->page_number;
            });

        if ($processPages->isEmpty()) {
            throw new \Exception('No pages found to merge for PDF process.');
        }

        // Initialize FPDI for merging
        $mergedPdf = new Fpdi();

        foreach ($processPages as $processPage) {
            // Determine which file path to use
            $filePath = $processPage->pdf_processed_path 
                ? $processPage->pdf_processed_path 
                : $processPage->pdf_original_path;

            if (!$filePath) {
                throw new \Exception("No file path available for process page {$processPage->id}.");
            }

            $fullPath = Storage::path($filePath);

            if (!file_exists($fullPath)) {
                throw new \Exception("PDF file not found: {$fullPath}");
            }

            // Import the page
            try {
                $pageCount = $mergedPdf->setSourceFile($fullPath);
                
                // Import all pages from this PDF (should be 1 page for process pages)
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $mergedPdf->AddPage();
                    $tplIdx = $mergedPdf->importPage($pageNo);
                    $mergedPdf->useTemplate($tplIdx);
                }
            } catch (\Exception $e) {
                throw new \Exception("Error importing page from {$filePath}: " . $e->getMessage());
            }
        }

        // Generate unique filename and store the merged PDF
        $mergedPath = 'pdf_process/merged/' . uniqid('merged_') . '.pdf';
        
        try {
            Storage::put($mergedPath, $mergedPdf->Output('S'));
        } catch (\Exception $e) {
            throw new \Exception('Failed to store merged PDF: ' . $e->getMessage());
        }

        // Update the PDF process with the final path
        $pdfProcess->update(['pdf_final_path' => $mergedPath]);

        return $mergedPath;
    }
}