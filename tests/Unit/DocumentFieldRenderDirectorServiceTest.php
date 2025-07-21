<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentPage;
use App\Models\PdfProcess;
use App\Models\PdfProcessPage;
use App\Services\DocumentFieldRenderDirectorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DocumentFieldRenderDirectorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_fields_by_pdf_process_page_groups_correctly()
    {
        // Arrange: Create a document with 3 pages, each with 3 fields
        $document = Document::factory()->create();
        $pages = DocumentPage::factory()->count(3)
		->for($document)
		->recycle($document)
		->create();


        $fields = collect();
        foreach ($pages as $page) {
            $fields = $fields->merge(
                DocumentField::factory()
					->recycle($document)
					->count(3)
					->create(
						[
							'document_page_id' => $page->id,
						]
					)
            );
        }

        foreach ($fields as $field) {
            DocumentFieldValue::factory()
            ->as($field->type)
            ->create([
                'document_field_id' => $field->id,
            ]);
        }


        // Create a PdfProcess and PdfProcessPages for the document
        $pdfProcess = PdfProcess::factory()->create(['document_id' => $document->id]);
        foreach ($pages as $page) {
            PdfProcessPage::factory()
			->recycle($pdfProcess)
				->create([
                'pdf_process_id' => $pdfProcess->id,
                'document_page_id' => $page->id,
            ]);
        }

		

        // Reload relations
        $document->refresh();
        $pdfProcess->refresh();

		// dd($document->with('pdfProcess.pages.documentPage')->first());

        $service = new DocumentFieldRenderDirectorService();

        // Act
        $grouped = $service->groupFieldsByPdfProcessPage($fields);

        // Assert: There should be 3 groups, each with 3 fields
        $this->assertCount(3, $grouped);
        foreach ($grouped as $fieldsForPage) {
            $this->assertCount(3, $fieldsForPage);
            foreach ($fieldsForPage as $field) {
                $this->assertInstanceOf(DocumentField::class, $field);
            }
        }
    }
} 