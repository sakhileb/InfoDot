<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Solutions;
use App\Models\Steps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for solution management functionality
 * 
 * Tests solution creation with steps, viewing, listing, and search
 * Requirements: FR-4, TR-1
 */
class SolutionManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can create a solution with steps
     */
    public function test_authenticated_users_can_create_solution_with_steps(): void
    {
        $user = User::factory()->create();

        $solutionData = [
            'solution_title' => 'How to Deploy Laravel Application',
            'solution_description' => 'A comprehensive guide to deploying Laravel apps',
            'tags' => 'laravel,deployment,devops',
            'duration' => 2,
            'duration_type' => 'hours',
            'solution_heading' => [
                'Step 1: Prepare Server',
                'Step 2: Configure Environment',
            ],
            'solution_body' => [
                'Install necessary dependencies and configure the server.',
                'Set up environment variables and database connections.',
            ],
        ];

        $response = $this->actingAs($user)->post(route('solutions.add'), $solutionData);

        $response->assertRedirect(route('solutions.index'));
        
        $this->assertDatabaseHas('solutions', [
            'user_id' => $user->id,
            'solution_title' => $solutionData['solution_title'],
            'solution_description' => $solutionData['solution_description'],
            'tags' => $solutionData['tags'],
            'duration' => $solutionData['duration'],
            'duration_type' => $solutionData['duration_type'],
        ]);

        // Verify steps were created
        $solution = Solutions::where('solution_title', $solutionData['solution_title'])->first();
        $this->assertCount(2, $solution->steps);
    }

    /**
     * Test that solution creation requires authentication
     */
    public function test_solution_creation_requires_authentication(): void
    {
        $response = $this->get(route('solutions.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test that solution creation validates required fields
     */
    public function test_solution_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('solutions.add'), []);

        $response->assertSessionHasErrors([
            'solution_title',
            'solution_description',
            'tags',
            'duration',
            'duration_type',
        ]);
    }

    /**
     * Test that users can view a single solution
     */
    public function test_users_can_view_single_solution(): void
    {
        $user = User::factory()->create();
        $solution = Solutions::factory()->create(['user_id' => $user->id]);
        
        // Create steps for the solution
        Steps::factory()->count(3)->create([
            'solution_id' => $solution->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('solutions.view', $solution->id));

        $response->assertStatus(200);
        $response->assertViewIs('solutions.view');
        $response->assertViewHas('solution');
    }

    /**
     * Test that viewing a non-existent solution returns 404
     */
    public function test_viewing_nonexistent_solution_returns_404(): void
    {
        $response = $this->get(route('solutions.view', 99999));

        $response->assertStatus(404);
    }

    /**
     * Test that users can view the solutions listing page
     */
    public function test_users_can_view_solutions_listing(): void
    {
        $response = $this->get(route('solutions.index'));

        $response->assertStatus(200);
        $response->assertViewIs('solutions.index');
    }

    /**
     * Test that solutions are searchable
     */
    public function test_solutions_are_searchable(): void
    {
        $user = User::factory()->create();
        
        $solution1 = Solutions::factory()->create([
            'user_id' => $user->id,
            'solution_title' => 'Laravel Deployment Guide',
            'solution_description' => 'How to deploy Laravel applications',
            'tags' => 'laravel,deployment',
        ]);

        $solution2 = Solutions::factory()->create([
            'user_id' => $user->id,
            'solution_title' => 'Vue.js Setup Tutorial',
            'solution_description' => 'Setting up Vue.js with Laravel',
            'tags' => 'vue,laravel',
        ]);

        // Search for Laravel-related solutions
        $results = Solutions::search('Laravel')->get();

        $this->assertTrue($results->contains($solution1));
        $this->assertTrue($results->contains($solution2));
    }

    /**
     * Test that solution steps are ordered correctly
     */
    public function test_solution_steps_are_ordered_correctly(): void
    {
        $user = User::factory()->create();
        $solution = Solutions::factory()->create(['user_id' => $user->id]);
        
        // Create steps in specific order
        $step1 = Steps::factory()->create([
            'solution_id' => $solution->id,
            'user_id' => $user->id,
            'solution_heading' => 'First Step',
            'created_at' => now()->subMinutes(3),
        ]);
        
        $step2 = Steps::factory()->create([
            'solution_id' => $solution->id,
            'user_id' => $user->id,
            'solution_heading' => 'Second Step',
            'created_at' => now()->subMinutes(2),
        ]);
        
        $step3 = Steps::factory()->create([
            'solution_id' => $solution->id,
            'user_id' => $user->id,
            'solution_heading' => 'Third Step',
            'created_at' => now()->subMinutes(1),
        ]);

        $solution = $solution->fresh();
        $steps = $solution->steps;

        $this->assertEquals('First Step', $steps[0]->solution_heading);
        $this->assertEquals('Second Step', $steps[1]->solution_heading);
        $this->assertEquals('Third Step', $steps[2]->solution_heading);
    }

    /**
     * Test that solutions can be filtered by tags
     */
    public function test_solutions_can_be_filtered_by_tags(): void
    {
        $user = User::factory()->create();
        
        $laravelSolution = Solutions::factory()->create([
            'user_id' => $user->id,
            'tags' => 'laravel,php,backend',
        ]);

        $vueSolution = Solutions::factory()->create([
            'user_id' => $user->id,
            'tags' => 'vue,javascript,frontend',
        ]);

        // Filter by Laravel tag
        $laravelSolutions = Solutions::where('tags', 'like', '%laravel%')->get();

        $this->assertTrue($laravelSolutions->contains($laravelSolution));
        $this->assertFalse($laravelSolutions->contains($vueSolution));
    }

    /**
     * Test that solution view includes eager-loaded relationships
     */
    public function test_solution_view_eager_loads_relationships(): void
    {
        $user = User::factory()->create();
        $solution = Solutions::factory()->create(['user_id' => $user->id]);
        
        Steps::factory()->count(2)->create([
            'solution_id' => $solution->id,
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('solutions.view', $solution->id));

        $response->assertStatus(200);
        
        $viewSolution = $response->viewData('solution');
        $this->assertTrue($viewSolution->relationLoaded('user'));
        $this->assertTrue($viewSolution->relationLoaded('steps'));
    }

    /**
     * Test that solution duration types are validated
     */
    public function test_solution_duration_types_are_validated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('solutions.add'), [
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'invalid_type', // Invalid duration type
            'solution_heading' => ['Step 1'],
            'solution_body' => ['Body 1'],
        ]);

        $response->assertSessionHasErrors('duration_type');
    }
}
