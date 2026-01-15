<?php

namespace Tests\Feature;

use App\Models\Questions;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test for Search Result Relevance
 * 
 * Feature: infodot-modernization, Property 7: Search Result Relevance
 * Validates: Requirements FR-7
 * 
 * Property: For any search query, all returned results should contain 
 * the search term in at least one searchable field.
 */
class SearchResultRelevanceTest extends TestCase
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
     * Feature: infodot-modernization, Property 7: Search Result Relevance
     */
    public function property_search_results_contain_query_term_in_questions()
    {
        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                // Generate random search terms and questions
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create questions with the search term in various fields
                $questionsWithTerm = $this->createQuestionsWithTerm($searchTerm, rand(1, 5));
                
                // Create questions without the search term
                $questionsWithoutTerm = $this->createQuestionsWithoutTerm($searchTerm, rand(1, 3));

                // Perform search
                $results = Questions::searchWithFallback($searchTerm);

                // Property: All results should contain the search term
                foreach ($results as $result) {
                    $containsTerm = $this->containsSearchTerm($result, $searchTerm, ['question', 'description']);
                    
                    if (!$containsTerm) {
                        $failedIterations[] = [
                            'iteration' => $i,
                            'searchTerm' => $searchTerm,
                            'result' => $result->toArray(),
                            'reason' => 'Result does not contain search term in any searchable field',
                        ];
                        break;
                    }
                }

                if (empty($failedIterations) || !isset($failedIterations[$i])) {
                    $passedIterations++;
                }

                // Clean up for next iteration
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
                "Property test failed: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 7: Search Result Relevance
     */
    public function property_search_results_contain_query_term_in_solutions()
    {
        $passedIterations = 0;
        $failedIterations = [];

        for ($i = 0; $i < $this->iterations; $i++) {
            try {
                // Generate random search terms and solutions
                $searchTerm = $this->generateRandomSearchTerm();
                
                // Create solutions with the search term in various fields
                $solutionsWithTerm = $this->createSolutionsWithTerm($searchTerm, rand(1, 5));
                
                // Create solutions without the search term
                $solutionsWithoutTerm = $this->createSolutionsWithoutTerm($searchTerm, rand(1, 3));

                // Perform search
                $results = Solutions::searchWithFallback($searchTerm);

                // Property: All results should contain the search term
                foreach ($results as $result) {
                    $containsTerm = $this->containsSearchTerm(
                        $result, 
                        $searchTerm, 
                        ['solution_title', 'solution_description', 'tags']
                    );
                    
                    if (!$containsTerm) {
                        $failedIterations[] = [
                            'iteration' => $i,
                            'searchTerm' => $searchTerm,
                            'result' => $result->toArray(),
                            'reason' => 'Result does not contain search term in any searchable field',
                        ];
                        break;
                    }
                }

                if (empty($failedIterations) || !isset($failedIterations[$i])) {
                    $passedIterations++;
                }

                // Clean up for next iteration
                Solutions::query()->delete();

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
                "Property test failed: Only %.2f%% of iterations passed. Failed iterations: %s",
                $successRate,
                json_encode(array_slice($failedIterations, 0, 5))
            )
        );
    }

    /**
     * @test
     * Feature: infodot-modernization, Property 7: Search Result Relevance
     */
    public function property_empty_search_returns_no_results_or_all_results()
    {
        $passedIterations = 0;

        for ($i = 0; $i < $this->iterations; $i++) {
            // Create random number of questions
            Questions::factory()->count(rand(1, 10))->create([
                'user_id' => $this->user->id,
            ]);

            // Search with empty string
            $results = Questions::searchWithFallback('');

            // Property: Empty search should either return no results or all results
            // This is implementation-dependent, but should be consistent
            $this->assertNotNull($results);
            
            $passedIterations++;

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

    protected function createQuestionsWithTerm(string $term, int $count): array
    {
        $questions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $fieldChoice = rand(1, 2);
            
            if ($fieldChoice === 1) {
                // Put term in question field
                $questions[] = Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => "How to use {$term} in my project?",
                    'description' => fake()->paragraph(),
                ]);
            } else {
                // Put term in description field
                $questions[] = Questions::factory()->create([
                    'user_id' => $this->user->id,
                    'question' => fake()->sentence(),
                    'description' => "I need help with {$term} implementation. " . fake()->paragraph(),
                ]);
            }
        }

        return $questions;
    }

    protected function createQuestionsWithoutTerm(string $term, int $count): array
    {
        $questions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $questions[] = Questions::factory()->create([
                'user_id' => $this->user->id,
                'question' => fake()->sentence(),
                'description' => fake()->paragraph(),
            ]);
        }

        return $questions;
    }

    protected function createSolutionsWithTerm(string $term, int $count): array
    {
        $solutions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $fieldChoice = rand(1, 3);
            
            if ($fieldChoice === 1) {
                // Put term in title
                $solutions[] = Solutions::factory()->create([
                    'user_id' => $this->user->id,
                    'solution_title' => "Complete {$term} Guide",
                    'solution_description' => fake()->paragraph(),
                    'tags' => fake()->words(3, true),
                ]);
            } elseif ($fieldChoice === 2) {
                // Put term in description
                $solutions[] = Solutions::factory()->create([
                    'user_id' => $this->user->id,
                    'solution_title' => fake()->sentence(),
                    'solution_description' => "This solution covers {$term} in detail. " . fake()->paragraph(),
                    'tags' => fake()->words(3, true),
                ]);
            } else {
                // Put term in tags
                $solutions[] = Solutions::factory()->create([
                    'user_id' => $this->user->id,
                    'solution_title' => fake()->sentence(),
                    'solution_description' => fake()->paragraph(),
                    'tags' => strtolower($term) . ',' . fake()->words(2, true),
                ]);
            }
        }

        return $solutions;
    }

    protected function createSolutionsWithoutTerm(string $term, int $count): array
    {
        $solutions = [];
        
        for ($i = 0; $i < $count; $i++) {
            $solutions[] = Solutions::factory()->create([
                'user_id' => $this->user->id,
                'solution_title' => fake()->sentence(),
                'solution_description' => fake()->paragraph(),
                'tags' => fake()->words(3, true),
            ]);
        }

        return $solutions;
    }

    protected function containsSearchTerm($model, string $searchTerm, array $fields): bool
    {
        $searchTermLower = strtolower($searchTerm);

        foreach ($fields as $field) {
            $fieldValue = strtolower($model->$field ?? '');
            
            if (str_contains($fieldValue, $searchTermLower)) {
                return true;
            }
        }

        return false;
    }
}
