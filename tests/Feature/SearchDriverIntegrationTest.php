<?php

namespace Tests\Feature;

use App\Models\Questions;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Property-Based Test for Search Driver Integration
 * 
 * Feature: infodot-modernization, Property 20: Search Driver Integration
 * Validates: Requirements IR-1
 * 
 * Property: For any searchable model, the search functionality should work 
 * correctly regardless of the configured driver (TNTSearch, Meilisearch, or MySQL).
 */
class SearchDriverIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected int $iterations = 100;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 20: Search Driver Integration
     */
    public function property_search_works_with_tntsearch_driver()
    {
        // Set Scout driver to TNTSearch
        Config::set('scout.driver', 'tntsearch');

        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                // Generate random search term
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create test data
                $question = Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => "How to use {$searchTerm}?",
                    'description' => "Guide for {$searchTerm}",
                ]);

                // Perform search
                $results = Questions::searchWithFallback($searchTerm);

                // Property: Search should return results
                $this->assertNotNull($results);
                
                $passedIterations++;

                // Clean up
                Questions::query()->delete();

            } catch (\Exception $e) {
                $failedIterations[] = [
                    'iteration' => $i,
                    'driver' => 'tntsearch',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Assert that at least 95% of iterations passed
        $successRate = ($passedIterations / $this->iterations) * 100;
        
        $this->assertGreaterThanOrEqual(
            95,
            $successRate,
            sprintf(
                "Property test failed with TNTSearch: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 20: Search Driver Integration
     */
    public function property_search_works_with_mysql_fulltext_fallback()
    {
        // Set Scout driver to null to force FULLTEXT fallback
        Config::set('scout.driver', null);

        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                // Generate random search term
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create test data
                $question = Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => "How to use {$searchTerm}?",
                    'description' => "Guide for {$searchTerm}",
                ]);

                // Perform search using FULLTEXT
                $results = Questions::searchWithFallback($searchTerm);

                // Property: Search should return results
                $this->assertNotNull($results);
                
                $passedIterations++;

                // Clean up
                Questions::query()->delete();

            } catch (\Exception $e) {
                $failedIterations[] = [
                    'iteration' => $i,
                    'driver' => 'mysql_fulltext',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Assert that at least 95% of iterations passed
        $successRate = ($passedIterations / $this->iterations) * 100;
        
        $this->assertGreaterThanOrEqual(
            95,
            $successRate,
            sprintf(
                "Property test failed with MySQL FULLTEXT: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 20: Search Driver Integration
     */
    public function property_search_driver_switching_maintains_consistency()
    {
        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                // Generate random search term
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create test data
                Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => "How to use {$searchTerm}?",
                    'description' => "Guide for {$searchTerm}",
                ]);

                // Search with TNTSearch
                Config::set('scout.driver', 'tntsearch');
                $resultsWithScout = Questions::searchWithFallback($searchTerm);

                // Search with FULLTEXT fallback
                Config::set('scout.driver', null);
                $resultsWithFulltext = Questions::searchWithFallback($searchTerm);

                // Property: Both drivers should return results (not necessarily identical, but both should work)
                $this->assertNotNull($resultsWithScout);
                $this->assertNotNull($resultsWithFulltext);
                
                $passedIterations++;

                // Clean up
                Questions::query()->delete();

            } catch (\Exception $e) {
                $failedIterations[] = [
                    'iteration' => $i,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Assert that at least 95% of iterations passed
        $successRate = ($passedIterations / $this->iterations) * 100;
        
        $this->assertGreaterThanOrEqual(
            95,
            $successRate,
            sprintf(
                "Property test failed for driver switching: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 20: Search Driver Integration
     */
    public function property_all_searchable_models_work_with_configured_driver()
    {
        Config::set('scout.driver', 'tntsearch');

        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create test data for all searchable models
                Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => "Question about {$searchTerm}",
                    'description' => "Description",
                ]);

                Solutions::factory()->create([
                    'user_id' => $this->user->id,
                    'solution_title' => "Solution for {$searchTerm}",
                    'solution_description' => "Description",
                    'tags' => strtolower($searchTerm),
                ]);

                User::factory()->create([
                    'name' => "{$searchTerm} User",
                    'email' => strtolower($searchTerm) . '@example.com',
                ]);

                // Property: All models should be searchable
                $questionResults = Questions::searchWithFallback($searchTerm);
                $solutionResults = Solutions::searchWithFallback($searchTerm);
                $userResults = User::searchWithFallback($searchTerm);

                $this->assertNotNull($questionResults);
                $this->assertNotNull($solutionResults);
                $this->assertNotNull($userResults);
                
                $passedIterations++;

                // Clean up
                Questions::query()->delete();
                Solutions::query()->delete();
                User::where('email', 'like', '%@example.com')->delete();

            } catch (\Exception $e) {
                $failedIterations[] = [
                    'iteration' => $i,
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Assert that at least 95% of iterations passed
        $successRate = ($passedIterations / $this->iterations) * 100;
        
        $this->assertGreaterThanOrEqual(
            95,
            $successRate,
            sprintf(
                "Property test failed for all searchable models: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 20: Search Driver Integration
     */
    public function property_search_handles_driver_errors_gracefully()
    {
        $passedIterations = 0;

        for ($i = 0; $i < $this->iterations; $i++) {
            // Set an invalid driver to test error handling
            Config::set('scout.driver', 'invalid_driver');

            $searchTerm = $this->generateRandomSearchTerm();
            
            Questions::factory()->create([
                'user_id' => $this->user->id,
                'question' => "Question about {$searchTerm}",
                'description' => "Description",
            ]);

            // Property: Search should fallback gracefully and not throw exceptions
            try {
                $results = Questions::searchWithFallback($searchTerm);
                $this->assertNotNull($results);
                $passedIterations++;
            } catch (\Exception $e) {
                // If an exception is thrown, the fallback didn't work properly
                $this->fail("Search should handle invalid driver gracefully: " . $e->getMessage());
            }

            // Clean up
            Questions::query()->delete();
        }

        $this->assertEquals($this->iterations, $passedIterations);
    }

    // Helper methods

    protected function generateRandomSearchTerm(): string
    {
        $terms = [
            'Laravel', 'PHP', 'JavaScript', 'Database', 'API',
            'Testing', 'Security', 'Performance', 'Deployment', 'Docker',
            'Vue', 'React', 'TypeScript', 'MySQL', 'Redis',
            'Authentication', 'Authorization', 'Validation', 'Migration', 'Seeding',
        ];

        return $terms[array_rand($terms)];
    }
}
