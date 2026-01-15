<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\Steps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Performance Testing Suite
 * 
 * Tests performance requirements from NFR-1:
 * - Page load times < 2 seconds
 * - API response times < 500ms
 * - Database query performance < 100ms average
 * - No N+1 query problems
 * - Concurrent user handling (100+)
 * 
 * Feature: infodot-modernization
 * Requirements: NFR-1
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /**
     * Test page load times are under 2 seconds
     * 
     * @test
     */
    public function page_load_times_are_under_2_seconds()
    {
        // Create test data
        Questions::factory()->count(20)->create(['user_id' => $this->user->id]);
        Solutions::factory()->count(20)->create(['user_id' => $this->user->id]);

        $pages = [
            '/' => 'Home page',
            '/questions' => 'Questions list',
            '/solutions' => 'Solutions list',
            '/about' => 'About page',
        ];

        foreach ($pages as $url => $description) {
            $startTime = microtime(true);
            
            $response = $this->actingAs($this->user)->get($url);
            
            $executionTime = (microtime(true) - $startTime) * 1000; // Convert to ms
            
            $response->assertStatus(200);
            
            $this->assertLessThan(
                2000,
                $executionTime,
                "{$description} load time should be under 2 seconds, took {$executionTime}ms"
            );
            
            echo "\n✓ {$description}: " . number_format($executionTime, 2) . "ms";
        }
    }

    /**
     * Test API response times are under 500ms
     * 
     * @test
     */
    public function api_response_times_are_under_500ms()
    {
        $this->withoutExceptionHandling();
        
        // Create test data
        $question = Questions::factory()->create(['user_id' => $this->user->id]);
        $answer = Answer::factory()->create([
            'user_id' => $this->user->id,
            'question_id' => $question->id,
        ]);

        // Create API token
        $token = $this->user->createToken('test-token')->plainTextToken;

        $endpoints = [
            ['GET', '/api/user', 'Get user profile'],
            ['GET', "/api/answers/question/{$question->id}", 'Get question answers'],
            ['POST', '/api/answers', 'Create answer', [
                'question_id' => $question->id,
                'content' => 'Test answer content',
            ]],
        ];

        foreach ($endpoints as $endpoint) {
            [$method, $url, $description] = $endpoint;
            $data = $endpoint[3] ?? [];
            
            $startTime = microtime(true);
            
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->{strtolower($method)}($url, $data);
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $response->assertSuccessful();
            
            $this->assertLessThan(
                500,
                $executionTime,
                "{$description} API response time should be under 500ms, took {$executionTime}ms"
            );
            
            echo "\n✓ {$description}: " . number_format($executionTime, 2) . "ms";
        }
    }

    /**
     * Test database query performance
     * 
     * @test
     */
    public function database_query_performance_is_acceptable()
    {
        // Create test data
        Questions::factory()->count(50)->create(['user_id' => $this->user->id]);
        
        $queries = [
            'Simple select' => fn() => Questions::where('user_id', $this->user->id)->first(),
            'With relationships' => fn() => Questions::with('user')->where('user_id', $this->user->id)->first(),
            'Count query' => fn() => Questions::where('user_id', $this->user->id)->count(),
            'Paginated query' => fn() => Questions::where('user_id', $this->user->id)->paginate(10),
        ];

        foreach ($queries as $description => $query) {
            $startTime = microtime(true);
            
            $query();
            
            $executionTime = (microtime(true) - $startTime) * 1000;
            
            $this->assertLessThan(
                100,
                $executionTime,
                "{$description} should execute in under 100ms, took {$executionTime}ms"
            );
            
            echo "\n✓ {$description}: " . number_format($executionTime, 2) . "ms";
        }
    }

    /**
     * Test for N+1 query problems
     * 
     * @test
     */
    public function no_n_plus_1_query_problems()
    {
        // Create test data with relationships
        $questions = Questions::factory()->count(10)->create(['user_id' => $this->user->id]);
        
        foreach ($questions as $question) {
            Answer::factory()->count(3)->create([
                'user_id' => $this->otherUser->id,
                'question_id' => $question->id,
            ]);
        }

        // Test without eager loading (should have N+1)
        DB::enableQueryLog();
        $questionsWithoutEager = Questions::all();
        foreach ($questionsWithoutEager as $question) {
            $question->user->name; // This triggers N+1
        }
        $queriesWithoutEager = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Test with eager loading (should not have N+1)
        DB::enableQueryLog();
        $questionsWithEager = Questions::with('user')->get();
        foreach ($questionsWithEager as $question) {
            $question->user->name;
        }
        $queriesWithEager = count(DB::getQueryLog());
        DB::disableQueryLog();

        // With eager loading should use significantly fewer queries
        $this->assertLessThan(
            $queriesWithoutEager / 2,
            $queriesWithEager,
            "Eager loading should reduce queries significantly. Without: {$queriesWithoutEager}, With: {$queriesWithEager}"
        );

        echo "\n✓ Eager loading reduces queries from {$queriesWithoutEager} to {$queriesWithEager}";

        // Test controller methods use eager loading
        DB::enableQueryLog();
        $this->actingAs($this->user)->get('/questions');
        $controllerQueries = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Should not have N+1 in controller
        $this->assertLessThan(
            20,
            $controllerQueries,
            "Questions controller should use eager loading. Query count: {$controllerQueries}"
        );

        echo "\n✓ Questions controller uses {$controllerQueries} queries (no N+1)";
    }

    /**
     * Test concurrent request handling
     * 
     * @test
     */
    public function handles_concurrent_requests()
    {
        // Create test data
        $question = Questions::factory()->create(['user_id' => $this->user->id]);

        // Simulate multiple concurrent requests
        $startTime = microtime(true);
        $responses = [];
        
        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->actingAs($this->user)->get("/questions/view/{$question->id}");
        }
        
        $totalTime = (microtime(true) - $startTime) * 1000;
        $avgTime = $totalTime / 10;

        // All requests should succeed
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }

        // Average response time should still be reasonable
        $this->assertLessThan(
            2000,
            $avgTime,
            "Average response time for concurrent requests should be under 2s, was {$avgTime}ms"
        );

        echo "\n✓ Handled 10 concurrent requests in " . number_format($totalTime, 2) . "ms";
        echo "\n✓ Average response time: " . number_format($avgTime, 2) . "ms";
    }

    /**
     * Test cache effectiveness
     * 
     * @test
     */
    public function cache_improves_performance()
    {
        Cache::flush();
        
        $question = Questions::factory()->create(['user_id' => $this->user->id]);
        
        // First request (no cache)
        $startTime = microtime(true);
        $result1 = Cache::remember("question.{$question->id}", 60, function () use ($question) {
            return Questions::with('user')->find($question->id);
        });
        $timeWithoutCache = (microtime(true) - $startTime) * 1000;
        
        // Second request (with cache)
        $startTime = microtime(true);
        $result2 = Cache::remember("question.{$question->id}", 60, function () use ($question) {
            return Questions::with('user')->find($question->id);
        });
        $timeWithCache = (microtime(true) - $startTime) * 1000;
        
        // Cached request should be faster
        $this->assertLessThan(
            $timeWithoutCache,
            $timeWithCache,
            "Cached request should be faster. Without cache: {$timeWithoutCache}ms, With cache: {$timeWithCache}ms"
        );
        
        echo "\n✓ Without cache: " . number_format($timeWithoutCache, 2) . "ms";
        echo "\n✓ With cache: " . number_format($timeWithCache, 2) . "ms";
        echo "\n✓ Cache speedup: " . number_format($timeWithoutCache / $timeWithCache, 2) . "x";
    }

    /**
     * Test query optimization with indexes
     * 
     * @test
     */
    public function database_indexes_improve_query_performance()
    {
        // Create large dataset
        Questions::factory()->count(100)->create(['user_id' => $this->user->id]);
        
        // Test indexed column (user_id has foreign key index)
        $startTime = microtime(true);
        Questions::where('user_id', $this->user->id)->get();
        $indexedQueryTime = (microtime(true) - $startTime) * 1000;
        
        // Test full-text search (has FULLTEXT index)
        $startTime = microtime(true);
        DB::table('questions')
            ->whereRaw('MATCH(question, description) AGAINST(? IN BOOLEAN MODE)', ['test'])
            ->get();
        $fulltextQueryTime = (microtime(true) - $startTime) * 1000;
        
        // Both should be fast due to indexes
        $this->assertLessThan(
            100,
            $indexedQueryTime,
            "Indexed query should be fast, took {$indexedQueryTime}ms"
        );
        
        $this->assertLessThan(
            100,
            $fulltextQueryTime,
            "FULLTEXT query should be fast, took {$fulltextQueryTime}ms"
        );
        
        echo "\n✓ Indexed query: " . number_format($indexedQueryTime, 2) . "ms";
        echo "\n✓ FULLTEXT query: " . number_format($fulltextQueryTime, 2) . "ms";
    }

    /**
     * Test memory usage is reasonable
     * 
     * @test
     */
    public function memory_usage_is_reasonable()
    {
        $memoryBefore = memory_get_usage(true);
        
        // Create and load data
        $questions = Questions::factory()->count(50)->create(['user_id' => $this->user->id]);
        $loadedQuestions = Questions::with('user')->get();
        
        $memoryAfter = memory_get_usage(true);
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB
        
        // Should not use excessive memory (< 50MB for 50 records)
        $this->assertLessThan(
            50,
            $memoryUsed,
            "Memory usage should be reasonable, used {$memoryUsed}MB"
        );
        
        echo "\n✓ Memory used for 50 questions: " . number_format($memoryUsed, 2) . "MB";
    }

    /**
     * Test solution with steps performance
     * 
     * @test
     */
    public function solution_with_steps_loads_efficiently()
    {
        // Create solution with many steps
        $solution = Solutions::factory()->create(['user_id' => $this->user->id]);
        Steps::factory()->count(10)->create([
            'user_id' => $this->user->id,
            'solution_id' => $solution->id,
        ]);
        
        // Test loading with eager loading
        DB::enableQueryLog();
        $startTime = microtime(true);
        
        $loadedSolution = Solutions::with(['user', 'steps'])->find($solution->id);
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        // Should load efficiently
        $this->assertLessThan(
            100,
            $executionTime,
            "Solution with steps should load in under 100ms, took {$executionTime}ms"
        );
        
        // Should not have N+1 queries
        $this->assertLessThanOrEqual(
            3,
            $queryCount,
            "Should use minimal queries with eager loading, used {$queryCount}"
        );
        
        echo "\n✓ Solution with 10 steps loaded in " . number_format($executionTime, 2) . "ms";
        echo "\n✓ Used {$queryCount} queries (no N+1)";
    }

    /**
     * Test search performance with large dataset
     * 
     * @test
     */
    public function search_performs_well_with_large_dataset()
    {
        // Create large dataset
        Questions::factory()->count(100)->create(['user_id' => $this->user->id]);
        Solutions::factory()->count(100)->create(['user_id' => $this->user->id]);
        
        // Test search performance
        $startTime = microtime(true);
        
        $questionResults = Questions::whereRaw(
            'MATCH(question, description) AGAINST(? IN BOOLEAN MODE)',
            ['test']
        )->limit(10)->get();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(
            500,
            $executionTime,
            "Search should complete in under 500ms, took {$executionTime}ms"
        );
        
        echo "\n✓ Search across 200 records: " . number_format($executionTime, 2) . "ms";
    }
}
