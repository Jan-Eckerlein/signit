<?php

namespace Tests\Feature;

use App\Enums\DocumentStatus;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Models\DocumentSigner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user for authentication
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

	protected function assertStatusOrDump($response, $status)
	{
		try {
			$response->assertStatus($status);
		} catch (\Exception $e) {
			dump('failed to assert status ' . $status . ' for response:');
			dump(json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			throw $e;
		}
		return $response;
	}

    public function test_index_lists_documents()
    {
        Document::factory()->count(2)->create(['owner_user_id' => $this->user->id]);
        $response = $this->getJson('/api/documents');
        $response->assertOk()->assertJsonStructure(['data']);
    }

    public function test_store_creates_document()
    {
        $payload = [
            'title' => 'Test Doc',
            'description' => 'Test Desc',
            'is_template' => false,
        ];
        $response = $this->postJson('/api/documents', $payload);
        $response->assertCreated()->assertJsonPath('data.title', 'Test Doc');
        $this->assertDatabaseHas('documents', ['title' => 'Test Doc']);
    }

    public function test_show_returns_document()
    {
        $doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
        $response = $this->getJson('/api/documents/' . $doc->id);
        $response->assertOk()->assertJsonPath('data.id', $doc->id);
    }

    public function test_update_modifies_document()
    {
        $doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
        $response = $this->putJson('/api/documents/' . $doc->id, [
            'title' => 'Updated Title',
            'description' => $doc->description,
            'is_template' => false,
        ]);
        $response->assertOk()->assertJsonPath('data.title', 'Updated Title');
        $this->assertDatabaseHas('documents', ['id' => $doc->id, 'title' => 'Updated Title']);
    }

    public function test_destroy_deletes_document()
    {
        $doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
        $response = $this->deleteJson('/api/documents/' . $doc->id);
        $response->assertOk();
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
    }

    public function test_open_for_signing_changes_status_and_notifies()
    {
        $doc = Document::factory()
			->create([
				'owner_user_id' => $this->user->id,
				'status' => DocumentStatus::DRAFT,
			]);

		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();

		$documentSigner = DocumentSigner::factory()
			->recycle($doc)
			->create();

		$documentField = DocumentField::factory()
			->count(3)
			->recycle([$documentPage, $documentSigner])
			->create();

        $response = $this->postJson('/api/documents/' . $doc->id . '/open-for-signing');
        $this->assertStatusOrDump($response, 200)->assertJsonPath('data.status', DocumentStatus::OPEN->value);
    }

	public function test_set_in_progress_fails_if_fields_are_unbound()
	{
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();
		$documentField = DocumentField::factory()
			->recycle($documentPage)
			->count(3)
			->create();

		$response = $this->postJson('/api/documents/' . $doc->id . '/open-for-signing');

		try {
			$response->assertStatus(422);
		} catch (\Exception $e) {
			dump(json_encode(json_decode($response->getContent()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
			throw $e;
		}
	}

    public function test_get_progress_returns_progress()
    {
        $doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
        $response = $this->getJson('/api/documents/' . $doc->id . '/progress');
        $response->assertOk()->assertJsonStructure(['data']);
    }
} 