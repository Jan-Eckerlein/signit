<?php

namespace Tests\Unit;

use App\Models\PdfProcessPage;
use App\Models\DocumentField;
use App\Services\PdfProcessRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfProcessRenderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_without_exception_and_returns_processed_path()
    {
        // Arrange: create a fake PDF file
        Storage::fake('local');

        // Copy the sample PDF from tests/files/sample.pdf to the fake storage location
        $samplePdfPath = base_path('tests/files/sample.pdf');
        $originalPath = 'pdf_process/originals/' . uniqid() . '_sample.pdf';
        $fakePdfContent = file_get_contents($samplePdfPath);

        Storage::disk('local')->put($originalPath, $fakePdfContent);

        // Create a PdfProcessPage
        $processPage = PdfProcessPage::factory()->create([
            'pdf_original_path' => $originalPath,
        ]);

        // Create DocumentFields
        $fields = DocumentField::factory()->count(2)->create([
            'document_page_id' => $processPage->document_page_id,
        ]);

        $service = new PdfProcessRenderService();

        // Act & Assert: should not throw and should return a string path
        $processedPath = null;
        try {
            $processedPath = $service->renderFieldsOnPage($processPage->id, $fields->pluck('id')->toArray());
        } catch (\Throwable $e) {
            $this->fail('Exception was thrown: ' . $e->getMessage());
        }

        $this->assertIsString($processedPath);
        $this->assertNotEmpty($processedPath);
    }
} 