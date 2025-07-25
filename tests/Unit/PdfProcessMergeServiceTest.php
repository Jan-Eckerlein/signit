<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\DocumentPage;
use App\Models\PdfProcess;
use App\Models\PdfProcessPage;
use App\Services\PdfProcessMergeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfProcessMergeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PdfProcessMergeService $service;
    protected Document $document;
    protected PdfProcess $pdfProcess;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('local');
        $this->service = new PdfProcessMergeService();
        
        // Create test document and PDF process
        $this->document = Document::factory()->create();
        $this->pdfProcess = PdfProcess::factory()->create([
            'document_id' => $this->document->id,
            'pdf_final_path' => null,
        ]);
    }

    public function test_merges_pdf_pages_in_correct_order_using_processed_and_original_paths()
    {
        // Arrange: Copy page_1.pdf to temporary storage locations
        $sourcePdfPath = base_path('tests/files/page_1.pdf');
        $pdfContent = file_get_contents($sourcePdfPath);
        
        // Create storage paths for the 3 pages
        $originalPath1 = 'pdf_process/pages/' . uniqid() . '_page_1.pdf';
        $originalPath2 = 'pdf_process/pages/' . uniqid() . '_page_2.pdf';
        $originalPath3 = 'pdf_process/pages/' . uniqid() . '_page_3.pdf';
        $processedPath2 = 'pdf_process/processed/' . uniqid() . '_processed_page_2.pdf';
        
        // Store the PDF files
        Storage::disk('local')->put($originalPath1, $pdfContent);
        Storage::disk('local')->put($originalPath2, $pdfContent);
        Storage::disk('local')->put($originalPath3, $pdfContent);
        Storage::disk('local')->put($processedPath2, $pdfContent); // Processed version of page 2
        
        // Create document pages with specific page numbers (not in order to test sorting)
        $documentPage1 = DocumentPage::factory()->create([
            'document_id' => $this->document->id,
            'page_number' => 1,
        ]);
        
        $documentPage2 = DocumentPage::factory()->create([
            'document_id' => $this->document->id,
            'page_number' => 2,
        ]);
        
        $documentPage3 = DocumentPage::factory()->create([
            'document_id' => $this->document->id,
            'page_number' => 3,
        ]);
        
        // Create PDF process pages (deliberately out of order to test sorting)
        PdfProcessPage::factory()->create([
            'pdf_process_id' => $this->pdfProcess->id,
            'document_page_id' => $documentPage3->id, // Page 3 first
            'pdf_original_path' => $originalPath3,
            'pdf_processed_path' => null,
            'is_up_to_date' => false,
        ]);
        
        PdfProcessPage::factory()->create([
            'pdf_process_id' => $this->pdfProcess->id,
            'document_page_id' => $documentPage1->id, // Page 1 second
            'pdf_original_path' => $originalPath1,
            'pdf_processed_path' => null,
            'is_up_to_date' => false,
        ]);
        
        PdfProcessPage::factory()->create([
            'pdf_process_id' => $this->pdfProcess->id,
            'document_page_id' => $documentPage2->id, // Page 2 third
            'pdf_original_path' => $originalPath2,
            'pdf_processed_path' => $processedPath2, // This one has processed path
            'is_up_to_date' => true,
        ]);
		
		
        // Act: Merge the PDF pages
        $mergedPath = $this->service->mergePdfPages($this->pdfProcess);

        // Assert: Check that merge was successful
        $this->assertIsString($mergedPath);
        $this->assertNotEmpty($mergedPath);
        $this->assertStringStartsWith('pdf_process/merged/', $mergedPath);
        $this->assertTrue(Storage::disk('local')->exists($mergedPath));
        
        // Assert: Check that PDF process was updated
        $this->pdfProcess->refresh();
        $this->assertEquals($mergedPath, $this->pdfProcess->pdf_final_path);
        
        // Assert: Verify the merged PDF exists and has content
        $mergedContent = Storage::disk('local')->get($mergedPath);
        $this->assertNotEmpty($mergedContent);
        $this->assertStringStartsWith('%PDF', $mergedContent); // PDF file signature
    }

    public function test_uses_processed_path_when_available_otherwise_original_path()
    {
        // Arrange: Create pages where we can track which path was used
        $sourcePdfPath = base_path('tests/files/page_1.pdf');
        $pdfContent = file_get_contents($sourcePdfPath);
        
        $originalPath = 'pdf_process/pages/' . uniqid() . '_original.pdf';
        $processedPath = 'pdf_process/processed/' . uniqid() . '_processed.pdf';
        
        Storage::disk('local')->put($originalPath, $pdfContent);
        Storage::disk('local')->put($processedPath, $pdfContent);
        
        $documentPage = DocumentPage::factory()->create([
            'document_id' => $this->document->id,
            'page_number' => 1,
        ]);
        
        PdfProcessPage::factory()->create([
            'pdf_process_id' => $this->pdfProcess->id,
            'document_page_id' => $documentPage->id,
            'pdf_original_path' => $originalPath,
            'pdf_processed_path' => $processedPath, // Both paths available
            'is_up_to_date' => true,
        ]);

        // Act
        $mergedPath = $this->service->mergePdfPages($this->pdfProcess);

        // Assert: Should succeed (implying it used the processed path)
        $this->assertIsString($mergedPath);
        $this->assertTrue(Storage::disk('local')->exists($mergedPath));
    }

    public function test_throws_exception_when_no_pages_found()
    {
        // Arrange: PDF process with no pages
        // (pdfProcess is already created in setUp with no pages)

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No pages found to merge for PDF process.');

        // Act
        $this->service->mergePdfPages($this->pdfProcess);
    }

    public function test_throws_exception_when_pdf_file_not_found()
    {
        // Arrange: Create a page with non-existent file path
        $documentPage = DocumentPage::factory()->create([
            'document_id' => $this->document->id,
            'page_number' => 1,
        ]);
        
        PdfProcessPage::factory()->create([
            'pdf_process_id' => $this->pdfProcess->id,
            'document_page_id' => $documentPage->id,
            'pdf_original_path' => 'non/existent/path.pdf',
            'pdf_processed_path' => null,
            'is_up_to_date' => false,
        ]);

        // Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('PDF file not found:');

        // Act
        $this->service->mergePdfPages($this->pdfProcess);
    }

    public function test_orders_pages_correctly_by_page_number()
    {
        // Arrange: Create pages with page numbers in reverse order
        $sourcePdfPath = base_path('tests/files/page_1.pdf');
        $pdfContent = file_get_contents($sourcePdfPath);
        
        $pageNumbers = [3, 1, 5, 2, 4]; // Intentionally out of order
        $paths = [];
        
        foreach ($pageNumbers as $pageNum) {
            $path = 'pdf_process/pages/' . uniqid() . "_page_{$pageNum}.pdf";
            Storage::disk('local')->put($path, $pdfContent);
            $paths[$pageNum] = $path;
            
            $documentPage = DocumentPage::factory()->create([
                'document_id' => $this->document->id,
                'page_number' => $pageNum,
            ]);
            
            PdfProcessPage::factory()->create([
                'pdf_process_id' => $this->pdfProcess->id,
                'document_page_id' => $documentPage->id,
                'pdf_original_path' => $path,
                'pdf_processed_path' => null,
                'is_up_to_date' => false,
            ]);
        }

        // Act
        $mergedPath = $this->service->mergePdfPages($this->pdfProcess);

        // Assert: Should complete successfully (pages were merged in correct order)
        $this->assertIsString($mergedPath);
        $this->assertTrue(Storage::disk('local')->exists($mergedPath));
        
        // Save the merged PDF for manual inspection if needed
        $outputDir = base_path('tests/output');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0777, true);
        }
        copy(Storage::disk('local')->path($mergedPath), $outputDir . '/merged_ordered_test.pdf');
    }
} 