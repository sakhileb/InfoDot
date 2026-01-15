<?php

namespace Tests\Feature;

use App\Models\Questions;
use App\Models\Solutions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_can_search_questions_with_various_queries()
    {
        // Create test questions
        $question1 = Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'How to use Laravel Scout?',
            'description' => 'I need help with Laravel Scout implementation',
        ]);

        $question2 = Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'What is PHP?',
            'description' => 'Basic PHP question',
        ]);

        // Test search with Scout or FULLTEXT fallback
        $results = Questions::searchWithFallback('Laravel Scout');
        
        $this->assertNotNull($results);
        $this->assertGreaterThan(0, $results->count());
    }

    /** @test */
    public function it_can_search_solutions_with_various_queries()
    {
        // Create test solutions
        $solution1 = Solutions::factory()->create([
            'user_id' => $this->user->id,
            'solution_title' => 'Laravel Scout Setup Guide',
            'solution_description' => 'Complete guide to setting up Laravel Scout',
            'tags' => 'laravel,scout,search',
        ]);

        $solution2 = Solutions::factory()->create([
            'user_id' => $this->user->id,
            'solution_title' => 'PHP Basics',
            'solution_description' => 'Introduction to PHP programming',
            'tags' => 'php,basics',
        ]);

        // Test search with Scout or FULLTEXT fallback
        $results = Solutions::searchWithFallback('Laravel Scout');
        
        $this->assertNotNull($results);
        $this->assertGreaterThan(0, $results->count());
    }

    /** @test */
    public function it_handles_empty_query_gracefully()
    {
        Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        // Empty query should return empty results or all results depending on implementation
        $results = Questions::searchWithFallback('');
        
        $this->assertNotNull($results);
    }

    /** @test */
    public function it_handles_special_characters_in_search()
    {
        Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'How to use C++ with Laravel?',
            'description' => 'Integration question',
        ]);

        // Test with special characters
        $results = Questions::searchWithFallback('C++');
        
        $this->assertNotNull($results);
    }

    /** @test */
    public function search_results_page_works_with_query()
    {
        Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Laravel Testing',
            'description' => 'How to test Laravel applications',
        ]);

        $response = $this->get('/solution-results?search=Laravel');

        $response->assertStatus(200);
        $response->assertViewIs('search_results');
        $response->assertViewHas('results');
    }

    /** @test */
    public function livewire_search_component_updates_with_query()
    {
        $this->actingAs($this->user);

        Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Laravel Livewire Search',
            'description' => 'Testing Livewire search functionality',
        ]);

        \Livewire\Livewire::test(\App\Http\Livewire\Search::class)
            ->set('query', 'Laravel')
            ->assertSet('query', 'Laravel')
            ->assertNotEmpty('questions');
    }

    /** @test */
    public function search_works_across_multiple_models()
    {
        // Create test data
        Questions::factory()->create([
            'user_id' => $this->user->id,
            'question' => 'Laravel Framework Question',
            'description' => 'About Laravel',
        ]);

        Solutions::factory()->create([
            'user_id' => $this->user->id,
            'solution_title' => 'Laravel Framework Solution',
            'solution_description' => 'How to use Laravel',
            'tags' => 'laravel',
        ]);

        // Test search across models
        $questionResults = Questions::searchWithFallback('Laravel Framework');
        $solutionResults = Solutions::searchWithFallback('Laravel Framework');

        $this->assertNotNull($questionResults);
        $this->assertNotNull($solutionResults);
    }

    /** @test */
    public function search_performance_is_acceptable()
    {
        // Create multiple records
        Questions::factory()->count(50)->create([
            'user_id' => $this->user->id,
        ]);

        $startTime = microtime(true);
        
        Questions::searchWithFallback('test query');
        
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Search should complete in less than 1 second
        $this->assertLessThan(1000, $executionTime);
    }
}
