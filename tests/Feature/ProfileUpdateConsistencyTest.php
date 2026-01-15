<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Property-based test for profile update consistency
 * 
 * Feature: infodot-modernization, Property 9: Profile Update Consistency
 * 
 * Property: For any user profile update, the changes should be immediately reflected in subsequent profile retrievals.
 * Validates: Requirements FR-9
 */
class ProfileUpdateConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test profile name update consistency
     * 
     * @test
     */
    public function property_profile_name_update_consistency(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create([
                'name' => 'Original Name',
            ]);

            $newName = 'Updated Name ' . $i;

            // Update profile
            $this->actingAs($user);
            
            Livewire::test(UpdateProfileInformationForm::class)
                ->set('state', [
                    'name' => $newName,
                    'email' => $user->email,
                ])
                ->call('updateProfileInformation');

            // Immediately retrieve and verify
            $updatedUser = User::find($user->id);
            $this->assertEquals($newName, $updatedUser->name);

            // Verify through fresh query
            $freshUser = User::where('id', $user->id)->first();
            $this->assertEquals($newName, $freshUser->name);
        }
    }

    /**
     * Test profile email update consistency
     * 
     * @test
     */
    public function property_profile_email_update_consistency(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create([
                'email' => 'original@example.com',
            ]);

            $newEmail = "updated{$i}@example.com";

            // Update profile
            $this->actingAs($user);
            
            Livewire::test(UpdateProfileInformationForm::class)
                ->set('state', [
                    'name' => $user->name,
                    'email' => $newEmail,
                ])
                ->call('updateProfileInformation');

            // Immediately retrieve and verify
            $updatedUser = User::find($user->id);
            $this->assertEquals($newEmail, $updatedUser->email);
        }
    }

    /**
     * Test profile photo update consistency
     * 
     * @test
     */
    public function property_profile_photo_update_consistency(): void
    {
        Storage::fake('public');

        // Run property test with multiple iterations
        for ($i = 0; $i < 20; $i++) { // Fewer iterations for file operations
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $this->actingAs($user);

            $photo = UploadedFile::fake()->image("photo{$i}.jpg");

            // Update profile photo
            Livewire::test(UpdateProfileInformationForm::class)
                ->set('photo', $photo)
                ->call('updateProfileInformation');

            // Immediately retrieve and verify
            $updatedUser = User::find($user->id);
            $this->assertNotNull($updatedUser->profile_photo_path);

            // Verify through fresh query
            $freshUser = User::where('id', $user->id)->first();
            $this->assertEquals($updatedUser->profile_photo_path, $freshUser->profile_photo_path);
        }
    }

    /**
     * Test multiple profile updates maintain consistency
     * 
     * @test
     */
    public function property_multiple_profile_updates_consistency(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $this->actingAs($user);

            // Perform multiple updates
            $updates = [
                ['name' => "Name Update 1-{$i}", 'email' => "email1-{$i}@example.com"],
                ['name' => "Name Update 2-{$i}", 'email' => "email2-{$i}@example.com"],
                ['name' => "Name Update 3-{$i}", 'email' => "email3-{$i}@example.com"],
            ];

            foreach ($updates as $update) {
                Livewire::test(UpdateProfileInformationForm::class)
                    ->set('state', $update)
                    ->call('updateProfileInformation');

                // Verify each update is immediately reflected
                $freshUser = User::find($user->id);
                $this->assertEquals($update['name'], $freshUser->name);
                $this->assertEquals($update['email'], $freshUser->email);
            }
        }
    }

    /**
     * Test profile update consistency across sessions
     * 
     * @test
     */
    public function property_profile_update_consistency_across_sessions(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $newName = "Updated Name {$i}";

            // Update in one session
            $this->actingAs($user);
            Livewire::test(UpdateProfileInformationForm::class)
                ->set('state', [
                    'name' => $newName,
                    'email' => $user->email,
                ])
                ->call('updateProfileInformation');

            // Logout and login again (new session)
            $this->post('/logout');
            $this->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

            // Verify update persists across sessions
            $freshUser = User::find($user->id);
            $this->assertEquals($newName, $freshUser->name);
        }
    }
}
