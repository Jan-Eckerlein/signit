<?php

namespace Tests\Feature\Controllers;

use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Events\SignatureCompletedEvent;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentSigner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DocumentSignerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }


    public function test_signer_cannot_complete_signature_if_field_is_not_filled(): void
    {
        $document = Document::factory()
            ->recycle($this->user)
            ->hasDocumentSigners(1)
            ->hasDocumentPages(1)
            ->create();

        $documentField = DocumentField::factory()
            ->create([
                'document_page_id' => $document->documentPages()->first()->id,
                'document_signer_id' => $document->documentSigners()->first()->id,
            ]);

        $document->status = DocumentStatus::OPEN;
        $document->save();

        $signerUser = $document->documentSigners()->first()->user;

        $response = $this->actingAs($signerUser)
            ->postJson('/api/document-signers/' . $document->documentSigners()->first()->id . '/complete-signature', [
            'electronic_signature_disclosure_accepted' => true,
        ]);

        $response->assertStatus(422);
    }



    /**
     * A basic feature test example.
     */
    public function test_signer_can_complete_signature_and_fire_event(): void
    {
        Event::fake();
        $document = Document::factory()
            ->hasDocumentPages(1)
            ->create();

        $documentSigner = DocumentSigner::factory()
            ->create([
                'document_id' => $document->id,
                'user_id' => $this->user->id,
            ]);

        $documentField = DocumentField::factory()
            ->create([
                'document_page_id' => $document->documentPages()->first()->id,
                'document_signer_id' => $documentSigner->id,
                'type' => DocumentFieldType::TEXT,
            ]);

        $document->status = DocumentStatus::OPEN;
        $document->save();

        $documentFieldValue = DocumentFieldValue::factory()
            ->as(DocumentFieldType::TEXT)
            ->create([
                'document_field_id' => $documentField->id,
            ]);

        $document->status = DocumentStatus::IN_PROGRESS;

        $document->save();


        $response = $this->postJson('/api/document-signers/' . $documentSigner->id . '/complete-signature', [
            'electronic_signature_disclosure_accepted' => true,
        ]);

        $this->assertStatusOrDump($response, 200);

        $completedSigner = DocumentSigner::find($documentSigner->id);
        $this->assertTrue($completedSigner->isSignatureCompleted());

        Event::assertDispatched(SignatureCompletedEvent::class);
    }
}
