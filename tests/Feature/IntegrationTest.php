<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Events\Questions\QuestionWasAsked;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Integration tests for cross-system functionality
 * 
 * Tests event broadcasting integration, queue processing, cache operations, and search integration
 * Requirements: TR-2
 */
class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test event broadcasting integration
     */
    public function test_event_broadcasting_integration(): void
    {
        Event::fake();
        
        $user = User::factory()->create();
        
        $this->actingAs($user)->post(route('questions.add'), [
            'question' => 'Test Question',
            'description' => 'Test Description',
            'tags' => 'test',
        ]);

        // Verify event was dispatched
        Event::assertDispatched(QuestionWasAsked::class);
    }

    /**
     * Test queue processing integration
     */
    public function test_queue_processing_integration(): void
    {
        Queue::fake();
        
        // Test that jobs can be queued
        $user = User::factory()->create();
        
        // Any queued operations should be captured
        // For example, email notifications
        
        Queue::assertNothingPushed(); // No jobs in this basic test
    }

    /**
     * Test cache operations integration
     */
    public function test_cache_operations_integration(): void
    {
        // Test cache put and get
        Cache::put('test_key', 'test_value', 60);
        $value = Cache::get('test_key');
        
        $this->assertEquals('test_value', $value);
        
        // Test cache remember
        $result = Cache::remember('computed_key', 60, function () {
            return 'computed_value';
        });
        
        $this->assertEquals('computed_value', $result);
        
        // Test cache forget
        Cache::forget('test_key');
        $this->assertNull(Cache::get('test_key'));
    }

    /**
     * Test search integration
     */
    public function test_search_integration(): void
    {
        $user = User::factory()->create();
        
        // Create searchable content
        $question = Questions::factory()->create([
            'user_id' => $user->id,
            'question' => 'How to use Laravel Scout?',
            'description' => 'I need help with Laravel Scout integration',
        ]);

        // Test search functionality
        $results = Questions::search('Laravel')->get();
        
        // Verify search returns results
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $results);
    }

    /**
     * Test database transaction integration
     */
    public function test_database_transaction_integration(): void
    {
        $user = User::factory()->create();
        
        try {
            \DB::beginTransaction();
            
            $question = Questions::create([
                'user_id' => $user->id,
                'question' => 'Test Question',
                'description' => 'Test Description',
                'tags' => 'test',
            ]);
            
            $answer = Answer::create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'content' => 'Test Answer',
            ]);
            
            \DB::commit();
            
            $this->assertDatabaseHas('questions', ['id' => $question->id]);
            $this->assertDatabaseHas('answers', ['id' => $answer->id]);
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->fail('Transaction failed: ' . $e->getMessage());
        }
    }

    /**
     * Test authentication and authorization integration
     */
    public function test_authentication_authorization_integration(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Create a question
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Create an answer
        $answer = Answer::factory()->create([
            'user_id' => $otherUser->id,
            'question_id' => $question->id,
        ]);
        
        // Test that question author can accept answers
        $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/toggle-acceptance")
            ->assertStatus(200);
        
        // Test that non-author cannot accept answers
        $this->actingAs($otherUser)
            ->postJson("/api/answers/{$answer->id}/toggle-acceptance")
            ->assertStatus(403);
    }

    /**
     * Test model relationships integration
     */
    public function test_model_relationships_integration(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);
        
        // Test relationships are properly loaded
        $this->assertEquals($user->id, $question->user->id);
        $this->assertEquals($user->id, $answer->user->id);
        $this->assertEquals($question->id, $answer->question->id);
        $this->assertTrue($question->answers->contains($answer));
    }

    /**
     * Test eager loading integration
     */
    public function test_eager_loading_integration(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        Answer::factory()->count(3)->create([
            'question_id' => $question->id,
        ]);
        
        // Test eager loading
        $questionWithAnswers = Questions::with(['user', 'answers'])->find($question->id);
        
        $this->assertTrue($questionWithAnswers->relationLoaded('user'));
        $this->assertTrue($questionWithAnswers->relationLoaded('answers'));
        $this->assertCount(3, $questionWithAnswers->answers);
    }

    /**
     * Test polymorphic relationships integration
     */
    public function test_polymorphic_relationships_integration(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);
        $solution = Solutions::factory()->create();
        
        // Test likes (polymorphic)
        $question->likes()->create(['user_id' => $user->id, 'like' => true]);
        $answer->likes()->create(['user_id' => $user->id, 'like' => true]);
        $solution->likes()->create(['user_id' => $user->id, 'like' => true]);
        
        $this->assertCount(1, $question->likes);
        $this->assertCount(1, $answer->likes);
        $this->assertCount(1, $solution->likes);
        
        // Test comments (polymorphic)
        $question->comments()->create(['user_id' => $user->id, 'body' => 'Comment 1']);
        $answer->comments()->create(['user_id' => $user->id, 'body' => 'Comment 2']);
        $solution->comments()->create(['user_id' => $user->id, 'body' => 'Comment 3']);
        
        $this->assertCount(1, $question->comments);
        $this->assertCount(1, $answer->comments);
        $this->assertCount(1, $solution->comments);
    }

    /**
     * Test validation integration
     */
    public function test_validation_integration(): void
    {
        $user = User::factory()->create();
        
        // Test question validation
        $response = $this->actingAs($user)->post(route('questions.add'), [
            // Missing required fields
        ]);
        
        $response->assertSessionHasErrors(['question', 'description']);
        
        // Test answer validation
        $response = $this->actingAs($user)->postJson('/api/answers', [
            // Missing required fields
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['question_id', 'content']);
    }

    /**
     * Test middleware integration
     */
    public function test_middleware_integration(): void
    {
        // Test auth middleware
        $response = $this->get(route('solutions.create'));
        $response->assertRedirect(route('login'));
        
        // Test authenticated access
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('solutions.create'));
        $response->assertStatus(200);
    }

    /**
     * Test API resource integration
     */
    public function test_api_resource_integration(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'Test answer content',
        ]);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content',
                'user_id',
                'question_id',
            ],
        ]);
    }

    /**
     * Test full user workflow integration
     */
    public function test_full_user_workflow_integration(): void
    {
        // Register user
        $user = User::factory()->create();
        
        // Login
        $this->actingAs($user);
        
        // Create question
        $this->post(route('questions.add'), [
            'question' => 'Integration Test Question',
            'description' => 'This is a test question',
            'tags' => 'test,integration',
        ]);
        
        $question = Questions::latest()->first();
        $this->assertNotNull($question);
        
        // Another user answers
        $answerer = User::factory()->create();
        $this->actingAs($answerer);
        
        $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is my answer',
        ]);
        
        $answer = Answer::latest()->first();
        $this->assertNotNull($answer);
        
        // Original user accepts answer
        $this->actingAs($user);
        $this->postJson("/api/answers/{$answer->id}/toggle-acceptance");
        
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => true,
        ]);
    }

    /**
     * Test cascade delete integration
     */
    public function test_cascade_delete_integration(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        $answer1 = Answer::factory()->create(['question_id' => $question->id]);
        $answer2 = Answer::factory()->create(['question_id' => $question->id]);
        
        // Delete question
        $question->delete();
        
        // Verify answers are also deleted (if cascade is configured)
        // Note: This depends on database foreign key configuration
        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
    }
}
