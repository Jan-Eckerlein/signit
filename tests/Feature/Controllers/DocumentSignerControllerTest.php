<?php

namespace Tests\Feature\Controllers;

use App\Events\SignatureCompletedEvent;
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
    /**
     * A basic feature test example.
     */
    public function test_user_can_complete_signature(): void
    {
        $documentSigner = DocumentSigner::factory()
            ->recycle($this->user)
            ->create();

        $response = $this->postJson('/api/document-signers/' . $documentSigner->id . '/complete-signature', [
            'electronic_signature_disclosure_accepted' => true,
        ]);

        $response->assertStatus(200);

    }

    public function test_complete_signature_fires_event(): void
    {
        Event::fake();

        $documentSigner = DocumentSigner::factory()
            ->recycle($this->user)
            ->create();

        $this->postJson('/api/document-signers/' . $documentSigner->id . '/complete-signature', [
            'electronic_signature_disclosure_accepted' => true,
        ]);


        Event::assertDispatched(SignatureCompletedEvent::class);
    }
}
