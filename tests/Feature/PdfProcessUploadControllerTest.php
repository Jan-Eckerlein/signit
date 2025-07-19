<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\PdfProcess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfProcessUploadControllerTest extends TestCase
{
    use RefreshDatabase;

	protected $user;
	protected $document;
	protected $pdfProcess;
	protected $file;
	protected $pageCount = 3;

	public function setUp(): void
	{
		parent::setUp();
		Storage::fake('local');

		$this->user = User::factory()->create();
		$this->actingAs($this->user);

		$this->document = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$this->pdfProcess = PdfProcess::create(['document_id' => $this->document->id]);

		$this->file = new \Illuminate\Http\UploadedFile(
			base_path('tests/files/sample.pdf'),
			'sample.pdf',
			'application/pdf',
			null,
			true
		);
	}

    public function test_uploads_pdf_file_to_pdf_process()
    {
        $response = $this->postJson('/api/pdf-process-uploads', [
            'pdf_process_id' => $this->pdfProcess->id,
            'pdfs' => [$this->file],
            'orders' => [1],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Pdf process upload created successfully']);

        // Assert the file was stored in the 'uploads' directory
        $this->assertTrue(Storage::disk('local')->exists('uploads/' . $this->file->hashName()));
    }

	public function test_uploaded_files_are_split_into_pages()
	{
        $response = $this->postJson('/api/pdf-process-uploads', [
            'pdf_process_id' => $this->pdfProcess->id,
            'pdfs' => [$this->file],
            'orders' => [1],
        ]);

		$this->assertEquals($this->pageCount, $this->pdfProcess->pages()->count());
	}
} 