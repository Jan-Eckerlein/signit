<?php

namespace Tests\Unit;

use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Models\Document;
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

		$document = Document::factory()->create();


        Storage::disk('local')->put($originalPath, $fakePdfContent);

        // Create a PdfProcessPage
        $processPage = PdfProcessPage::factory()
			->recycle($document)
			->create([
				'pdf_original_path' => $originalPath,
			]);

		$documentPage = $processPage->documentPage;

        // Create DocumentFields
        $fields = DocumentField::factory()->count(6)
			->recycle($documentPage)
			->create();

		$document->status = DocumentStatus::OPEN;
		$document->save();


		foreach ($fields as $field) {
			$value = DocumentFieldValue::factory()
				->as($field->type)
				->recycle($sign)
				->create([
					'document_field_id' => $field->id,
				]);
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