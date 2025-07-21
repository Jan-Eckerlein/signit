<?php

namespace Tests\Feature\Controllers;

use App\Jobs\ProcessSignatureImage;
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
        $this->assertSoftDeleted($sign);
    }

    public function test_and_restore_sign()
    {
        $sign = Sign::factory()->create(['user_id' => $this->user->id]);
        // Soft delete
        $sign->delete();
        $this->assertSoftDeleted($sign);
        // Restore
        $response = $this->postJson('/api/signs/' . $sign->id . '/restore');
        $response->assertOk();
        $this->assertDatabaseHas('signs', ['id' => $sign->id, 'deleted_at' => null]);
    }

    public function test_force_delete()
    {
        $sign = Sign::factory()->create(['user_id' => $this->user->id]);

        // Force delete
        $response = $this->deleteJson('/api/signs/' . $sign->id . '/force');
        $response->assertOk();
        $this->assertDatabaseMissing('signs', ['id' => $sign->id]);
    }
} 