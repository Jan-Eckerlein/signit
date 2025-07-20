<?php

namespace Tests\Feature\Lifecycles;

use App\Events\DocumentOpenedEvent;
use App\Mail\DocumentOpenedMailable;
use App\Mail\DocumentOpenedMagicLinkMailable;
use App\Models\Document;
use App\Models\User;
use App\Models\DocumentSigner;
use App\Models\DocumentField;
use App\Models\DocumentPage;
use App\Services\UserAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DocumentOpenedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_correct_emails_when_document_is_opened()
    {
        Mail::fake();
		Queue::fake();

        // Create users
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $anonUser = User::factory()->anonymous()->create(['email' => 'anon@example.com']);

        // Create document
        $document = Document::factory()->create();
        $page = DocumentPage::factory()->create([
            'document_id' => $document->id,
            'page_number' => 1,
        ]);

        // Create signers
        $signer1 = DocumentSigner::factory()->create([
            'document_id' => $document->id,
            'user_id' => $existingUser->id,
        ]);
        $signer2 = DocumentSigner::factory()->create([
            'document_id' => $document->id,
            'user_id' => $anonUser->id,
        ]);

        // Create fields
        DocumentField::factory()->create([
            'document_page_id' => $page->id,
            'document_signer_id' => $signer1->id,
        ]);
        DocumentField::factory()->create([
            'document_page_id' => $page->id,
            'document_signer_id' => $signer2->id,
        ]);

        // Fire the event
        event(new DocumentOpenedEvent($document, UserAgent::fake($existingUser)));

        // Assert emails sent
        Mail::assertQueued(DocumentOpenedMailable::class, function ($mail) use ($existingUser) {
            return $mail->hasTo($existingUser->email);
        });
        Mail::assertQueued(DocumentOpenedMagicLinkMailable::class, function ($mail) use ($anonUser) {
            return $mail->hasTo($anonUser->email);
        });
        Mail::assertQueuedCount(2);
    }

	public function test_log_is_created_when_document_opened_email_is_sent()
	{
		// Arrange
		$user = User::factory()->create();
		$document = Document::factory()->create();

		// Act: Send the mail (not queue)
		Mail::to($user->email)->queue(new DocumentOpenedMailable($document, $user));

		// Assert: A DocumentLog was created
		$this->assertDatabaseHas('document_logs', [
			'document_id' => $document->id,
			'icon' => \App\Enums\Icon::SEND->value,
			'text' => "Document opened email sent to {$user->email}",
		]);
	}
} 