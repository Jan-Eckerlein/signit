<?php

namespace Tests\Unit;

use App\Enums\DocumentFieldType;
use App\Models\PdfProcessPage;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\Sign;
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

		$sampleSignPath = base_path('tests/files/sample-signature.png');
		$newSignPath = 'signs/' . uniqid() . '_sample-signature.png';
		Storage::disk('local')->put($newSignPath, file_get_contents($sampleSignPath));

		$sign = Sign::factory()->create([
			'image_path' => $newSignPath,
		]);


        Storage::disk('local')->put($originalPath, $fakePdfContent);

        // Create a PdfProcessPage
        $processPage = PdfProcessPage::factory()->create([
            'pdf_original_path' => $originalPath,
        ]);

        // Create DocumentFields
        $fields = DocumentField::factory()->count(6)->create([
            'document_page_id' => $processPage->document_page_id,
        ]);

		foreach ($fields as $field) {
			$value = DocumentFieldValue::factory()->as($field->type)->create([
				'document_field_id' => $field->id,
			]);

			if ($field->type === DocumentFieldType::SIGNATURE) {
				$value->signatureSign()->associate($sign)->save();
			}
		}

        $service = new PdfProcessRenderService();

        // Act & Assert: should not throw and should return a string path
        $processedPath = null;
		$processedPath = $service->renderFieldsOnPage($processPage->id, $fields->pluck('id')->toArray());

        $this->assertIsString($processedPath);
        $this->assertNotEmpty($processedPath);

        $realPath = base_path('tests/output/latest_processed.pdf');
        if (!is_dir(dirname($realPath))) {
            mkdir(dirname($realPath), 0777, true);
        }
        copy(Storage::disk('local')->path($processedPath), $realPath);
    }
} 