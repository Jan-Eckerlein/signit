<?php

namespace Tests\Feature\Controllers;

use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Jobs\ProcessSignatureImage;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentPage;
use App\Models\DocumentSigner;
use App\Models\Sign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SignControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_returns_signs_for_user()
    {
        Sign::factory()->count(2)->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/signs');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_store_creates_sign_and_dispatches_job()
    {
        Queue::fake();
        $sampleSignaturePath = base_path('tests/files/sample-signature.png');
        $uploadedFile = new UploadedFile(
            $sampleSignaturePath,
            'sample-signature.png',
            'image/png',
            null,
            true
        );
        $payload = [
            'name' => 'Test Sign',
            'description' => 'Test Desc',
            'image' => $uploadedFile,
        ];
        $response = $this->postJson('/api/signs', $payload);
        $response->assertCreated();
        $this->assertDatabaseHas('signs', ['name' => 'Test Sign']);
        Queue::assertPushed(ProcessSignatureImage::class);
    }

    public function test_show_returns_sign()
    {
        $sign = Sign::factory()->create(['user_id' => $this->user->id]);
        $response = $this->getJson('/api/signs/' . $sign->id);
        $response->assertOk()->assertJsonPath('data.id', $sign->id);
    }

    public function test_update_modifies_sign()
    {
        $sign = Sign::factory()->create(['user_id' => $this->user->id]);
        $response = $this->putJson('/api/signs/' . $sign->id, [
            'name' => 'Updated Name',
            'description' => $sign->description,
        ]);
        $response->assertOk()->assertJsonPath('data.name', 'Updated Name');
        $this->assertDatabaseHas('signs', ['id' => $sign->id, 'name' => 'Updated Name']);
    }

    public function test_destroy_deletes_sign()
    {
        $sign = Sign::factory()->create(['user_id' => $this->user->id]);
        $response = $this->deleteJson('/api/signs/' . $sign->id);
        $response->assertOk();
        $this->assertDatabaseMissing('signs', ['id' => $sign->id]);
    }

	public function test_destroy_archives_sign()
	{
		$sign = Sign::factory()->create(['user_id' => $this->user->id]);

        $document = Document::factory()->create();
        $documentPage = DocumentPage::factory()->create(['document_id' => $document->id]);
        $documentSigner = DocumentSigner::factory()->create(['document_id' => $document->id, 'user_id' => $this->user->id]);

        $documentField = DocumentField::factory()->as(DocumentFieldType::SIGNATURE)->create([
			'document_page_id' => $documentPage->id,
			'document_signer_id' => $documentSigner->id,
		]);

        $document->status = DocumentStatus::OPEN;
        $document->save();

		$documentFieldValue = DocumentFieldValue::factory()
        ->as(DocumentFieldType::SIGNATURE, $sign->id)
        ->create([
            'document_field_id' => $documentField->id,
        ]);

        $sign->refresh();
            
		$response = $this->deleteJson('/api/signs/' . $sign->id);
		$response->assertOk();
		$this->assertDatabaseHas('signs', ['id' => $sign->id, 'archived_at' => now()]);
	}

	public function test_unarchive_unarchives_sign()
	{
		$sign = Sign::factory()->create(['user_id' => $this->user->id]);

        $document = Document::factory()->create();
        $documentPage = DocumentPage::factory()->create(['document_id' => $document->id]);
        $documentSigner = DocumentSigner::factory()->create(['document_id' => $document->id, 'user_id' => $this->user->id]);

        $documentField = DocumentField::factory()->as(DocumentFieldType::SIGNATURE)->create([
			'document_page_id' => $documentPage->id,
			'document_signer_id' => $documentSigner->id,
		]);

		$documentFieldValue = DocumentFieldValue::factory()
			->as(DocumentFieldType::SIGNATURE, $sign->id)
			->create([
				'document_field_id' => $documentField->id,
			]);
            
		$response = $this->deleteJson('/api/signs/' . $sign->id);
		$response->assertOk();
		$this->assertDatabaseHas('signs', ['id' => $sign->id, 'archived_at' => now()]);
		$response = $this->postJson('/api/signs/' . $sign->id . '/unarchive');
		$response->assertOk();
		$this->assertDatabaseHas('signs', ['id' => $sign->id, 'archived_at' => null]);
	}
} 