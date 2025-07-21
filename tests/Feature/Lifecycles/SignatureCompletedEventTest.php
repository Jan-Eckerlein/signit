<?php

namespace Tests\Feature\Lifecycles;

use App\Enums\DocumentFieldType;
use App\Enums\DocumentStatus;
use App\Events\SignatureCompletedEvent;
use App\Models\Document;
use App\Models\DocumentField;
use App\Models\DocumentFieldValue;
use App\Models\DocumentPage;
use App\Models\DocumentSigner;
use App\Models\User;
use App\Services\UserAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignatureCompletedEventTest extends TestCase
{
    use RefreshDatabase;

    protected Document $document;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create();
        $this->document = Document::factory()
            ->hasDocumentSigners(3)
            ->hasDocumentPages(1)
            ->create([
                'owner_user_id' => $this->owner->id,
            ]);

        foreach ($this->document->documentSigners as $signer) {
            DocumentField::factory()
                ->as(DocumentFieldType::TEXT)
                ->create([
                'document_signer_id' => $signer->id,
                'document_page_id' => $this->document->documentPages()->first()->id,
            ]);
        }
        
        $this->document->status = DocumentStatus::OPEN;
        $this->document->save();
    }

    protected function createValueForField(DocumentField $field): void
    {
        DocumentFieldValue::factory()
            ->as(DocumentFieldType::TEXT)
            ->create([
                'document_field_id' => $field->id,
            ]);
    }


    /**
     * A basic feature test example.
     */
    public function test_first_signature_completed_sets_document_to_in_progress(): void
    {
        $signer = $this->document->documentSigners()->first();
        $this->createValueForField($signer->documentFields()->first());
        $signer->completeSignature();
        $this->document->refresh();

        SignatureCompletedEvent::dispatch($signer, UserAgent::fake($signer->user));

        $this->assertDatabaseHas('documents', [
            'id' => $this->document->id,
            'status' => DocumentStatus::IN_PROGRESS,
        ]);
    }
}
