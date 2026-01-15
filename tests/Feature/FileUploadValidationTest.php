<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @test
 * Feature: infodot-modernization, Property 8: File Upload Validation
 * 
 * Property: For any file upload attempt, files exceeding size limits or with 
 * invalid types should be rejected before storage.
 * 
 * Validates: Requirements FR-8
 */
class FileUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
    }

    /**
     * Property Test: Files exceeding size limit should be rejected
     * 
     * @test
     */
    public function property_files_exceeding_size_limit_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with 100 random file sizes above the limit
        for ($i = 0; $i < 100; $i++) {
            // Generate random size between 10MB and 50MB (limit is 10MB)
            $sizeInKB = rand(10241, 51200);
            
            // Create a fake file exceeding the size limit
            $file = UploadedFile::fake()->create(
                'large_file_' . $i . '.pdf',
                $sizeInKB
            );

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File ' . $i,
            ]);

            // Assert the file was rejected
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('file');
            
            // Assert no file was stored
            $this->assertDatabaseMissing('files', [
                'name' => 'Test File ' . $i,
            ]);
        }
    }

    /**
     * Property Test: Files with invalid MIME types should be rejected
     * 
     * @test
     */
    public function property_files_with_invalid_mime_types_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Invalid file extensions that should be rejected
        $invalidExtensions = [
            'exe', 'bat', 'sh', 'cmd', 'com', 'scr', 'vbs', 'js', 'jar',
            'app', 'deb', 'rpm', 'dmg', 'pkg', 'msi', 'apk'
        ];

        // Test with 100 random invalid file types
        for ($i = 0; $i < 100; $i++) {
            $extension = $invalidExtensions[array_rand($invalidExtensions)];
            
            // Create a fake file with invalid extension
            $file = UploadedFile::fake()->create(
                'malicious_file_' . $i . '.' . $extension,
                100 // 100KB
            );

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File ' . $i,
            ]);

            // Assert the file was rejected
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('file');
            
            // Assert no file was stored
            $this->assertDatabaseMissing('files', [
                'name' => 'Test File ' . $i,
            ]);
        }
    }

    /**
     * Property Test: Valid files within limits should be accepted
     * 
     * @test
     */
    public function property_valid_files_within_limits_are_accepted(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Valid file extensions
        $validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'];

        // Test with 100 random valid files
        for ($i = 0; $i < 100; $i++) {
            $extension = $validExtensions[array_rand($validExtensions)];
            $sizeInKB = rand(1, 10240); // 1KB to 10MB
            
            // Create a fake valid file
            $file = UploadedFile::fake()->create(
                'valid_file_' . $i . '.' . $extension,
                $sizeInKB
            );

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File ' . $i,
            ]);

            // Assert the file was accepted
            $response->assertStatus(201);
            $response->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully',
            ]);
            
            // Assert file was stored in database
            $this->assertDatabaseHas('files', [
                'name' => 'Test File ' . $i,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Property Test: File validation should prevent storage before rejection
     * 
     * @test
     */
    public function property_invalid_files_are_not_stored_before_rejection(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $initialFileCount = File::count();
        $initialMediaCount = \Spatie\MediaLibrary\MediaCollections\Models\Media::count();

        // Test with 100 invalid file attempts
        for ($i = 0; $i < 100; $i++) {
            // Randomly choose between size violation or type violation
            if ($i % 2 === 0) {
                // Size violation
                $file = UploadedFile::fake()->create('large_' . $i . '.pdf', 15000);
            } else {
                // Type violation
                $file = UploadedFile::fake()->create('malicious_' . $i . '.exe', 100);
            }

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File ' . $i,
            ]);

            // Assert rejection
            $response->assertStatus(422);
        }

        // Assert no files were stored in database
        $this->assertEquals($initialFileCount, File::count());
        
        // Assert no media files were created
        $this->assertEquals($initialMediaCount, \Spatie\MediaLibrary\MediaCollections\Models\Media::count());
    }

    /**
     * Property Test: File validation should be consistent across multiple uploads
     * 
     * @test
     */
    public function property_file_validation_is_consistent(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test the same invalid file multiple times
        for ($i = 0; $i < 100; $i++) {
            $file = UploadedFile::fake()->create('test.exe', 100);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File',
            ]);

            // Should always be rejected
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('file');
        }

        // Test the same valid file multiple times
        for ($i = 0; $i < 100; $i++) {
            $file = UploadedFile::fake()->create('test_' . $i . '.pdf', 100);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Test File ' . $i,
            ]);

            // Should always be accepted
            $response->assertStatus(201);
        }
    }

    /**
     * Property Test: Empty or missing files should be rejected
     * 
     * @test
     */
    public function property_empty_or_missing_files_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with 100 requests without files
        for ($i = 0; $i < 100; $i++) {
            $response = $this->postJson('/files', [
                'name' => 'Test File ' . $i,
                // No file provided
            ]);

            // Assert rejection
            $response->assertStatus(422);
            $response->assertJsonValidationErrors('file');
            
            // Assert no file was stored
            $this->assertDatabaseMissing('files', [
                'name' => 'Test File ' . $i,
            ]);
        }
    }

    /**
     * Property Test: File validation should handle edge cases at size boundary
     * 
     * @test
     */
    public function property_file_validation_handles_size_boundary_correctly(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test files at the exact size limit (10MB = 10240KB)
        $boundarySize = 10240;

        // Test 50 files at exactly the limit
        for ($i = 0; $i < 50; $i++) {
            $file = UploadedFile::fake()->create('boundary_' . $i . '.pdf', $boundarySize);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Boundary File ' . $i,
            ]);

            // Should be accepted (at limit, not exceeding)
            $response->assertStatus(201);
        }

        // Test 50 files just over the limit
        for ($i = 0; $i < 50; $i++) {
            $file = UploadedFile::fake()->create('over_' . $i . '.pdf', $boundarySize + 1);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => 'Over Limit File ' . $i,
            ]);

            // Should be rejected
            $response->assertStatus(422);
        }
    }
}
