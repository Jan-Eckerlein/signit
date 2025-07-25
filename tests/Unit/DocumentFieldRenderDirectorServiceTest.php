<?php

namespace Tests\Unit;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentPage;
use App\Models\DocumentSigner;
use App\Models\PdfProcess;
use App\Models\PdfProcessPage;
use App\Services\DocumentFieldRenderDirectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DocumentFieldRenderDirectorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_completed_field_ids_grouped_by_pdf_process_page_returns_only_completed_signers()
    {
        // Arrange: Create a document with 2 pages
        $document = Document::factory()->create(); // Create as DRAFT by default
        $pages = DocumentPage::factory()->count(2)
            ->for($document)
            ->create();

        // Create 2 signers - one completed, one incomplete
        $completedSigner = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);
        
        $incompleteSigner = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);

        // Create fields for both signers
        $completedFields = collect();
        $incompleteFields = collect();
        
        foreach ($pages as $pageIndex => $page) {
            // Add 2 fields for completed signer on each page
            for ($i = 0; $i < 2; $i++) {
                $completedFields->push(
                    DocumentField::factory()->create([
                        'document_page_id' => $page->id,
                        'document_signer_id' => $completedSigner->id,
                    ])
                );
            }
            
            // Add 1 field for incomplete signer on each page
            $incompleteFields->push(
                DocumentField::factory()->create([
                    'document_page_id' => $page->id,
                    'document_signer_id' => $incompleteSigner->id,
                ])
            );
        }

        // Update document status to OPEN to allow signature completion
        $document->status = DocumentStatus::OPEN;
        $document->save();

        // Refresh models to ensure they have the updated document status
        $completedSigner->refresh();
        $incompleteSigner->refresh();

        // Create field values for completed signer's fields (required for completion)
        foreach ($completedFields as $field) {
            DocumentFieldValue::factory()
                ->as($field->type)
                ->create([
                    'document_field_id' => $field->id,
                ]);
        }

        // Complete the signature for the completed signer
        $completedSigner->completeSignature();

        // Create a PdfProcess and PdfProcessPages
        $pdfProcess = PdfProcess::factory()->create(['document_id' => $document->id]);
        $pdfProcessPages = collect();
        foreach ($pages as $page) {
            $pdfProcessPages->push(
                PdfProcessPage::factory()
                    ->recycle($pdfProcess)
                    ->create([
                        'pdf_process_id' => $pdfProcess->id,
                        'document_page_id' => $page->id,
                    ])
            );
        }

        $service = new DocumentFieldRenderDirectorService();

        // Act
        $grouped = $service->getCompletedFieldIdsGroupedByPdfProcessPage($document->id);

        // Assert: Should only include fields from completed signer
        $this->assertCount(2, $grouped); // 2 pages
        
        $totalFieldIds = 0;
        foreach ($grouped as $pdfProcessPageId => $fieldIds) {
            $this->assertCount(2, $fieldIds); // 2 fields per page for completed signer
            $totalFieldIds += count($fieldIds);
            
            // Verify these are actually field IDs
            foreach ($fieldIds as $fieldId) {
                $this->assertIsInt($fieldId);
                
                // Verify the field belongs to the completed signer
                $field = DocumentField::find($fieldId);
                $this->assertEquals($completedSigner->id, $field->document_signer_id);
            }
        }
        
        $this->assertEquals(4, $totalFieldIds); // Total: 2 pages × 2 fields per page = 4 fields
    }
} 