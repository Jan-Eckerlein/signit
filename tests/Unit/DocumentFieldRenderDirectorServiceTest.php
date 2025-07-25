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

    public function test_get_completed_field_ids_for_pages_affected_by_signer_returns_all_completed_fields_on_affected_pages()
    {
        // Arrange: Create a document with 3 pages
        $document = Document::factory()->create();
        $pages = DocumentPage::factory()->count(3)
            ->for($document)
            ->create();

        // Create 3 signers - two completed, one incomplete
        $completedSignerA = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);
        
        $completedSignerB = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);
        
        $incompleteSignerC = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);

        // Create fields:
        // Page 1: SignerA (1 field), SignerB (1 field), SignerC (1 field) 
        // Page 2: SignerA (1 field), SignerB (1 field)
        // Page 3: SignerB (1 field), SignerC (1 field)
        $fieldsSignerA = collect();
        $fieldsSignerB = collect();
        $fieldsSignerC = collect();
        
        // Page 1: All signers have fields
        $fieldsSignerA->push(DocumentField::factory()->create([
            'document_page_id' => $pages[0]->id,
            'document_signer_id' => $completedSignerA->id,
        ]));
        $fieldsSignerB->push(DocumentField::factory()->create([
            'document_page_id' => $pages[0]->id,
            'document_signer_id' => $completedSignerB->id,
        ]));
        $fieldsSignerC->push(DocumentField::factory()->create([
            'document_page_id' => $pages[0]->id,
            'document_signer_id' => $incompleteSignerC->id,
        ]));
        
        // Page 2: Only SignerA and SignerB have fields
        $fieldsSignerA->push(DocumentField::factory()->create([
            'document_page_id' => $pages[1]->id,
            'document_signer_id' => $completedSignerA->id,
        ]));
        $fieldsSignerB->push(DocumentField::factory()->create([
            'document_page_id' => $pages[1]->id,
            'document_signer_id' => $completedSignerB->id,
        ]));
        
        // Page 3: Only SignerB and SignerC have fields (no SignerA)
        $fieldsSignerB->push(DocumentField::factory()->create([
            'document_page_id' => $pages[2]->id,
            'document_signer_id' => $completedSignerB->id,
        ]));
        $fieldsSignerC->push(DocumentField::factory()->create([
            'document_page_id' => $pages[2]->id,
            'document_signer_id' => $incompleteSignerC->id,
        ]));

        // Update document status and refresh signers
        $document->status = DocumentStatus::OPEN;
        $document->save();
        $completedSignerA->refresh();
        $completedSignerB->refresh();
        $incompleteSignerC->refresh();

        // Create field values for completed signers' fields
        foreach ($fieldsSignerA->concat($fieldsSignerB) as $field) {
            DocumentFieldValue::factory()
                ->as($field->type)
                ->create(['document_field_id' => $field->id]);
        }

        // Complete signatures for SignerA and SignerB
        $completedSignerA->completeSignature();
        $completedSignerB->completeSignature();

        // Create PdfProcess and PdfProcessPages
        $pdfProcess = PdfProcess::factory()->create(['document_id' => $document->id]);
        foreach ($pages as $page) {
            PdfProcessPage::factory()
                ->recycle($pdfProcess)
                ->create([
                    'pdf_process_id' => $pdfProcess->id,
                    'document_page_id' => $page->id,
                ]);
        }

        $service = new DocumentFieldRenderDirectorService();

        // Act: Get pages affected by SignerA (should be Page 1 and Page 2)
        $grouped = $service->getCompletedFieldIdsForPagesAffectedBySigner($completedSignerA->id);

        // Assert: Should return 2 pages (Page 1 and Page 2), with ALL completed signers' fields on those pages
        $this->assertCount(2, $grouped); // Page 1 and Page 2
        
        foreach ($grouped as $pdfProcessPageId => $fieldIds) {
            // Each page should have 2 fields (1 from SignerA + 1 from SignerB)
            // SignerC's fields should NOT be included because SignerC is not completed
            $this->assertCount(2, $fieldIds);
            
            // Verify all field IDs belong to completed signers
            foreach ($fieldIds as $fieldId) {
                $field = DocumentField::find($fieldId);
                $this->assertContains($field->document_signer_id, [$completedSignerA->id, $completedSignerB->id]);
                $this->assertNotEquals($incompleteSignerC->id, $field->document_signer_id);
            }
        }
        
        // Total should be 4 fields (2 pages × 2 completed signers per page)
        $totalFieldIds = collect($grouped)->flatten()->count();
        $this->assertEquals(4, $totalFieldIds);
    }

    public function test_get_completed_field_ids_for_pages_affected_by_signer_returns_empty_when_signer_not_completed()
    {
        // Arrange: Create a document with 2 pages
        $document = Document::factory()->create();
        $pages = DocumentPage::factory()->count(2)
            ->for($document)
            ->create();

        // Create 3 signers - only 2 will be completed, 1 will remain incomplete
        $completedSignerA = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);
        
        $completedSignerB = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);
        
        $incompleteSignerC = DocumentSigner::factory()->create([
            'document_id' => $document->id,
        ]);

        // Create 1 field per signer per page (2 pages × 3 signers = 6 fields total)
        $allFields = collect();
        $fieldsSignerA = collect();
        $fieldsSignerB = collect();
        $fieldsSignerC = collect();
        
        foreach ($pages as $page) {
            // Signer A field on this page
            $fieldA = DocumentField::factory()->create([
                'document_page_id' => $page->id,
                'document_signer_id' => $completedSignerA->id,
            ]);
            $fieldsSignerA->push($fieldA);
            $allFields->push($fieldA);
            
            // Signer B field on this page
            $fieldB = DocumentField::factory()->create([
                'document_page_id' => $page->id,
                'document_signer_id' => $completedSignerB->id,
            ]);
            $fieldsSignerB->push($fieldB);
            $allFields->push($fieldB);
            
            // Signer C field on this page (this signer will NOT complete)
            $fieldC = DocumentField::factory()->create([
                'document_page_id' => $page->id,
                'document_signer_id' => $incompleteSignerC->id,
            ]);
            $fieldsSignerC->push($fieldC);
            $allFields->push($fieldC);
        }

        // Update document status and refresh signers
        $document->status = DocumentStatus::OPEN;
        $document->save();
        $completedSignerA->refresh();
        $completedSignerB->refresh();
        $incompleteSignerC->refresh();

        // Create field values for ALL signers' fields (including the incomplete one)
        foreach ($allFields as $field) {
            DocumentFieldValue::factory()
                ->as($field->type)
                ->create(['document_field_id' => $field->id]);
        }

        // Complete signatures for SignerA and SignerB only
        // SignerC remains incomplete even though they have filled fields
        $completedSignerA->completeSignature();
        $completedSignerB->completeSignature();

        // Create PdfProcess and PdfProcessPages
        $pdfProcess = PdfProcess::factory()->create(['document_id' => $document->id]);
        foreach ($pages as $page) {
            PdfProcessPage::factory()
                ->recycle($pdfProcess)
                ->create([
                    'pdf_process_id' => $pdfProcess->id,
                    'document_page_id' => $page->id,
                ]);
        }

        $service = new DocumentFieldRenderDirectorService();

        // Act: Try to get pages affected by the INCOMPLETE signer (SignerC)
        $grouped = $service->getCompletedFieldIdsForPagesAffectedBySigner($incompleteSignerC->id);

        // Assert: Should return empty array because SignerC has not completed their signature
        // Even though SignerC has fields on both pages, the method should return empty
        // because we only want to re-render when a signer has COMPLETED their signature
        $this->assertEmpty($grouped);
        $this->assertEquals([], $grouped);
    }
} 