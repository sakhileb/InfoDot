<?php

namespace Tests\Feature;

use App\Models\Solutions;
use App\Models\Steps;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test: Solution Step Ordering
 * 
 * Feature: infodot-modernization, Property 4: Solution Step Ordering
 * Validates: Requirements FR-4
 * 
 * Property: For any solution with multiple steps, the steps should always be 
 * retrieved in the order they were created.
 */
class SolutionStepOrderingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that solution steps maintain creation order.
     * Runs 100+ iterations with random data to verify the property holds.
     *
     * @test
     */
    public function property_solution_step_ordering(): void
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create a user and solution
            $user = User::factory()->create();
            $solution = Solutions::create([
                'user_id' => $user->id,
                'solution_title' => "Test solution {$i}",
                'solution_description' => "Test description {$i}",
                'tags' => 'test,solution',
                'duration' => rand(1, 100),
                'duration_type' => $this->getRandomDurationType(),
                'steps' => rand(2, 10),
            ]);

            // Create random number of steps (2-10)
            $stepCount = rand(2, 10);
            $createdSteps = [];
            
            for ($j = 0; $j < $stepCount; $j++) {
                // Add a small delay to ensure different timestamps
                usleep(1000); // 1ms delay
                
                $step = Steps::create([
                    'user_id' => $user->id,
                    'solution_id' => $solution->id,
                    'solution_heading' => "Step {$j} heading",
                    'solution_body' => "Step {$j} body content for iteration {$i}",
                ]);
                
                $createdSteps[] = [
                    'id' => $step->id,
                    'heading' => $step->solution_heading,
                    'created_at' => $step->created_at,
                ];
            }

            // Retrieve steps through the relationship
            $retrievedSteps = $solution->steps()->get();

            // Verify we got all steps
            $this->assertCount(
                $stepCount,
                $retrievedSteps,
                "Should retrieve all {$stepCount} steps (iteration {$i})"
            );

            // Verify steps are in creation order
            for ($j = 0; $j < $stepCount; $j++) {
                $this->assertEquals(
                    $createdSteps[$j]['id'],
                    $retrievedSteps[$j]->id,
                    "Step {$j} should be in position {$j} (iteration {$i})"
                );
                
                $this->assertEquals(
                    $createdSteps[$j]['heading'],
                    $retrievedSteps[$j]->solution_heading,
                    "Step {$j} heading should match (iteration {$i})"
                );
            }

            // Verify timestamps are in ascending order
            for ($j = 1; $j < $stepCount; $j++) {
                $this->assertGreaterThanOrEqual(
                    $retrievedSteps[$j - 1]->created_at,
                    $retrievedSteps[$j]->created_at,
                    "Step {$j} should be created after step " . ($j - 1) . " (iteration {$i})"
                );
            }

            // Clean up
            foreach ($retrievedSteps as $step) {
                $step->forceDelete();
            }
            $solution->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Test that step ordering is maintained even with gaps in IDs.
     *
     * @test
     */
    public function property_solution_step_ordering_with_gaps(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create();
            $solution = Solutions::create([
                'user_id' => $user->id,
                'solution_title' => "Test solution {$i}",
                'solution_description' => "Test description {$i}",
                'tags' => 'test',
                'duration' => 10,
                'duration_type' => 'days',
                'steps' => 5,
            ]);

            // Create steps with intentional gaps
            $stepCount = 5;
            $createdSteps = [];
            
            for ($j = 0; $j < $stepCount; $j++) {
                usleep(1000);
                
                $step = Steps::create([
                    'user_id' => $user->id,
                    'solution_id' => $solution->id,
                    'solution_heading' => "Step {$j}",
                    'solution_body' => "Body {$j}",
                ]);
                
                $createdSteps[] = $step;
                
                // Delete middle step to create a gap
                if ($j === 2) {
                    $step->forceDelete();
                    array_pop($createdSteps);
                }
            }

            // Retrieve remaining steps
            $retrievedSteps = $solution->steps()->get();

            // Should have 4 steps (5 created - 1 deleted)
            $this->assertCount(
                4,
                $retrievedSteps,
                "Should have 4 steps after deletion (iteration {$i})"
            );

            // Verify remaining steps are still in creation order
            $expectedOrder = [0, 1, 3, 4]; // Indices of non-deleted steps
            for ($j = 0; $j < count($retrievedSteps); $j++) {
                $expectedIndex = $expectedOrder[$j];
                $this->assertEquals(
                    "Step {$expectedIndex}",
                    $retrievedSteps[$j]->solution_heading,
                    "Step at position {$j} should be step {$expectedIndex} (iteration {$i})"
                );
            }

            // Clean up
            foreach ($retrievedSteps as $step) {
                $step->forceDelete();
            }
            $solution->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Test ordering with concurrent-like creation (minimal time gaps).
     *
     * @test
     */
    public function property_solution_step_ordering_rapid_creation(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create();
            $solution = Solutions::create([
                'user_id' => $user->id,
                'solution_title' => "Rapid test {$i}",
                'solution_description' => "Testing rapid creation",
                'tags' => 'test',
                'duration' => 5,
                'duration_type' => 'hours',
                'steps' => 3,
            ]);

            // Create steps rapidly (simulating near-concurrent creation)
            $stepIds = [];
            for ($j = 0; $j < 3; $j++) {
                $step = Steps::create([
                    'user_id' => $user->id,
                    'solution_id' => $solution->id,
                    'solution_heading' => "Rapid step {$j}",
                    'solution_body' => "Body {$j}",
                ]);
                $stepIds[] = $step->id;
            }

            // Retrieve steps
            $retrievedSteps = $solution->steps()->get();

            // Verify order matches creation order (by ID)
            for ($j = 0; $j < 3; $j++) {
                $this->assertEquals(
                    $stepIds[$j],
                    $retrievedSteps[$j]->id,
                    "Rapid step {$j} should maintain order (iteration {$i})"
                );
            }

            // Clean up
            foreach ($retrievedSteps as $step) {
                $step->forceDelete();
            }
            $solution->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Get a random duration type.
     */
    private function getRandomDurationType(): string
    {
        $types = ['hours', 'days', 'weeks', 'months', 'years', 'infinite'];
        return $types[array_rand($types)];
    }
}
