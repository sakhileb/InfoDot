<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadDebugTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function debug_file_upload()
    {
        Storage::fake('media');
        
        $user = User::factory()->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->postJson('/files', [
            'file' => $file,
            'name' => 'Test File',
        ]);

        // Dump the response for debugging
        if ($response->status() !== 201) {
            dump($response->json());
            dump($response->getContent());
        }

        $response->assertStatus(201);
    }
}
