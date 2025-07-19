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

    public function test_uploads_pdf_file_to_pdf_process()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $this->actingAs($user);

        $document = Document::factory()->create(['owner_user_id' => $user->id]);
        $pdfProcess = PdfProcess::create(['document_id' => $document->id]);

        $file = new \Illuminate\Http\UploadedFile(
            base_path('tests/files/sample.pdf'),
            'sample.pdf',
            'application/pdf',
            null,
            true
        );

        $response = $this->postJson('/api/pdf-process-uploads', [
            'pdf_process_id' => $pdfProcess->id,
            'pdfs' => [$file],
            'orders' => [1],
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Pdf process upload created successfully']);

        // Assert the file was stored in the 'uploads' directory
        $this->assertTrue(Storage::disk('local')->exists('uploads/' . $file->hashName()));
    }
} 