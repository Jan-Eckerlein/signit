<?php

namespace Tests\Unit;

use App\Services\SignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SignServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_process_and_store_signature_creates_png_in_storage()
    {
        // Arrange
        $service = new SignService();
        $sampleSignaturePath = base_path('tests/files/sample-signature.png');
        $uploadedFile = new UploadedFile(
            $sampleSignaturePath,
            'sample-signature.png',
            'image/png',
            null,
            true
        );

        // Act
        $path = $service->processAndStoreSignature($uploadedFile);

        // Assert
        $this->assertTrue(Storage::disk('local')->exists($path));
        $this->assertStringEndsWith('.png', $path);
    }
} 