<?php

namespace Tests\Feature\Controllers;

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
			->create([
				'document_page_id' => $documentPage->id,
				'document_signer_id' => $documentSigner->id,
			]);

        $response = $this->postJson('/api/documents/' . $doc->id . '/open-for-signing');
        $this->assertStatusOrDump($response, 200)->assertJsonPath('data.status', DocumentStatus::OPEN->value);
    }

	public function test_open_for_signing_fails_if_document_has_no_signers()
	{
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$response = $this->postJson('/api/documents/' . $doc->id . '/open-for-signing');
		$this->assertStatusOrDump($response, 422);
	}

	public function test_open_for_signing_fails_if_signer_has_no_fields()
	{
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();
		$documentSigner = DocumentSigner::factory()
			->recycle($doc)
			->create();
		$response = $this->postJson('/api/documents/' . $doc->id . '/open-for-signing');
		$this->assertStatusOrDump($response, 422);
	}

	public function test_set_in_progress_fails_if_fields_are_unbound()
	{
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();

		$documentField = DocumentField::factory()
			->count(3)
			->create([
				'document_page_id' => $documentPage->id,
			]);

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

	public function test_revert_to_draft_changes_status()
	{
		// Create a Document that can be opened for signing
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();
		$documentSigner = DocumentSigner::factory()
			->recycle($doc)
			->create();
		$documentField = DocumentField::factory()
			->count(3)
			->create([
				'document_page_id' => $documentPage->id,
				'document_signer_id' => $documentSigner->id,
			]);

		// Open the document for signing
		$doc->status = DocumentStatus::OPEN;
		$doc->save();

		// Revert the document to draft
		$response = $this->postJson('/api/documents/' . $doc->id . '/revert-to-draft');
		$response->assertOk()->assertJsonPath('data.status', DocumentStatus::DRAFT->value);
	}

	public function test_revert_to_draft_fails_if_document_is_not_open()
	{
		// Create a Document that can be opened for signing
		$doc = Document::factory()->create(['owner_user_id' => $this->user->id]);
		$documentPage = DocumentPage::factory()
			->recycle($doc)
			->create();
		$documentSigner = DocumentSigner::factory()
			->recycle($doc)
			->create();
		$documentField = DocumentField::factory()
			->count(3)
			->create([
				'document_page_id' => $documentPage->id,
				'document_signer_id' => $documentSigner->id,
			]);

		// Open the document for signing
		$doc->status = DocumentStatus::OPEN;
		$doc->save();

		// Set the document to in progress as if another user has signed it
		$doc->status = DocumentStatus::IN_PROGRESS;
		$doc->save();

		// Revert the document to draft
		$response = $this->postJson('/api/documents/' . $doc->id . '/revert-to-draft');
		$this->assertStatusOrDump($response, 422);
	}
} 