<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * @test
 * Feature: infodot-modernization, Property 23: Storage Driver Abstraction
 * 
 * Property: For any file storage operation, files should be stored and retrieved 
 * correctly regardless of the storage driver (local, S3, etc.).
 * 
 * Validates: Requirements IR-4
 */
class StorageDriverAbstractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up fake storage for testing
        Storage::fake('media');
        Storage::fake('public');
        Storage::fake('local');
    }

    /**
     * Property Test: Files stored on any disk should be retrievable
     * 
     * @test
     */
    public function property_files_stored_on_any_disk_are_retrievable(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with different storage disks
        $disks = ['media', 'public', 'local'];

        foreach ($disks as $disk) {
            // Configure media library to use this disk
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            // Test with 30 random files per disk
            for ($i = 0; $i < 30; $i++) {
                $file = UploadedFile::fake()->create("test_{$disk}_{$i}.pdf", 100);

                $response = $this->postJson('/files', [
                    'file' => $file,
                    'name' => "Test File {$disk} {$i}",
                ]);

                // Assert file was stored
                $response->assertStatus(201);

                // Retrieve the file from database
                $storedFile = File::where('name', "Test File {$disk} {$i}")->first();
                $this->assertNotNull($storedFile);

                // Assert file can be retrieved from storage
                $media = $storedFile->getFirstMedia('files');
                $this->assertNotNull($media);
                $this->assertTrue(Storage::disk($disk)->exists($media->getPath()));
            }
        }
    }

    /**
     * Property Test: Storage driver changes should not affect file operations
     * 
     * @test
     */
    public function property_storage_driver_changes_do_not_affect_operations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $disks = ['media', 'public'];

        // Test with 50 iterations of disk switching
        for ($i = 0; $i < 50; $i++) {
            $disk = $disks[$i % 2];
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            $file = UploadedFile::fake()->create("test_{$i}.pdf", 100);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => "Test File {$i}",
            ]);

            // Operations should succeed regardless of disk
            $response->assertStatus(201);
            $response->assertJson([
                'success' => true,
            ]);

            // File should be retrievable
            $storedFile = File::where('name', "Test File {$i}")->first();
            $this->assertNotNull($storedFile);
        }
    }

    /**
     * Property Test: File deletion works across all storage drivers
     * 
     * @test
     */
    public function property_file_deletion_works_across_all_drivers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $disks = ['media', 'public', 'local'];

        foreach ($disks as $disk) {
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            // Test with 30 files per disk
            for ($i = 0; $i < 30; $i++) {
                $file = UploadedFile::fake()->create("delete_test_{$disk}_{$i}.pdf", 100);

                $response = $this->postJson('/files', [
                    'file' => $file,
                    'name' => "Delete Test {$disk} {$i}",
                ]);

                $response->assertStatus(201);

                // Get the stored file
                $storedFile = File::where('name', "Delete Test {$disk} {$i}")->first();
                $media = $storedFile->getFirstMedia('files');
                $mediaPath = $media->getPath();

                // Delete the file
                $deleteResponse = $this->deleteJson("/files/{$storedFile->id}");
                $deleteResponse->assertStatus(200);

                // Assert file is deleted from database
                $this->assertDatabaseMissing('files', [
                    'id' => $storedFile->id,
                ]);

                // Assert file is deleted from storage
                $this->assertFalse(Storage::disk($disk)->exists($mediaPath));
            }
        }
    }

    /**
     * Property Test: File metadata is consistent across storage drivers
     * 
     * @test
     */
    public function property_file_metadata_consistent_across_drivers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $disks = ['media', 'public'];

        foreach ($disks as $disk) {
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            // Test with 50 files per disk
            for ($i = 0; $i < 50; $i++) {
                $fileName = "metadata_test_{$disk}_{$i}.pdf";
                $file = UploadedFile::fake()->create($fileName, 100);

                $response = $this->postJson('/files', [
                    'file' => $file,
                    'name' => "Metadata Test {$disk} {$i}",
                ]);

                $response->assertStatus(201);

                // Retrieve file and check metadata
                $storedFile = File::where('name', "Metadata Test {$disk} {$i}")->first();
                $media = $storedFile->getFirstMedia('files');

                // Assert metadata is present and correct
                $this->assertNotNull($media);
                $this->assertEquals($disk, $media->disk);
                $this->assertEquals('files', $media->collection_name);
                $this->assertNotNull($media->file_name);
                $this->assertNotNull($media->mime_type);
                $this->assertGreaterThan(0, $media->size);
            }
        }
    }

    /**
     * Property Test: File download works regardless of storage driver
     * 
     * @test
     */
    public function property_file_download_works_across_drivers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $disks = ['media', 'public'];

        foreach ($disks as $disk) {
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            // Test with 30 files per disk
            for ($i = 0; $i < 30; $i++) {
                $file = UploadedFile::fake()->create("download_test_{$disk}_{$i}.pdf", 100);

                $response = $this->postJson('/files', [
                    'file' => $file,
                    'name' => "Download Test {$disk} {$i}",
                ]);

                $response->assertStatus(201);

                // Get the stored file
                $storedFile = File::where('name', "Download Test {$disk} {$i}")->first();

                // Attempt to download
                $downloadResponse = $this->get("/files/{$storedFile->id}/download");
                
                // Should be successful regardless of disk
                $downloadResponse->assertStatus(200);
            }
        }
    }

    /**
     * Property Test: Storage abstraction handles concurrent operations
     * 
     * @test
     */
    public function property_storage_handles_concurrent_operations(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('media');
        Config::set('media-library.disk_name', 'media');

        // Simulate concurrent file uploads (100 files)
        $files = [];
        for ($i = 0; $i < 100; $i++) {
            $file = UploadedFile::fake()->create("concurrent_{$i}.pdf", 100);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => "Concurrent Test {$i}",
            ]);

            $response->assertStatus(201);
            $files[] = File::where('name', "Concurrent Test {$i}")->first();
        }

        // Assert all files were stored correctly
        $this->assertCount(100, $files);

        // Assert all files are retrievable
        foreach ($files as $storedFile) {
            $this->assertNotNull($storedFile);
            $media = $storedFile->getFirstMedia('files');
            $this->assertNotNull($media);
            $this->assertTrue(Storage::disk('media')->exists($media->getPath()));
        }
    }

    /**
     * Property Test: Storage driver configuration changes are respected
     * 
     * @test
     */
    public function property_storage_configuration_changes_are_respected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Test with 100 configuration changes
        for ($i = 0; $i < 100; $i++) {
            $disk = ($i % 2 === 0) ? 'media' : 'public';
            Config::set('media-library.disk_name', $disk);
            Storage::fake($disk);

            $file = UploadedFile::fake()->create("config_test_{$i}.pdf", 100);

            $response = $this->postJson('/files', [
                'file' => $file,
                'name' => "Config Test {$i}",
            ]);

            $response->assertStatus(201);

            // Verify file was stored on the correct disk
            $storedFile = File::where('name', "Config Test {$i}")->first();
            $media = $storedFile->getFirstMedia('files');
            
            $this->assertEquals($disk, $media->disk);
        }
    }
}
