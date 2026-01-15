<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\Steps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Checkpoint 8: Verify Controllers and Routes
 * 
 * This test suite verifies:
 * - All web routes are accessible
 * - All API endpoints work correctly
 * - Authentication is properly enforced
 * - Eager loading optimization prevents N+1 queries
 */
class ControllersAndRoutesCheckpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /**
     * Test: Web Routes - Public Routes
     */
    public function test_public_web_routes_are_accessible(): void
    {
        // Test welcome page
        $response = $this->get('/');
        $response->assertStatus(200);

        // Test about page
        $response = $this->get('/about');
        $response->assertStatus(200);

        // Test contact page
        $response = $this->get('/contact');
        $response->assertStatus(200);

        // Test FAQs page
        $response = $this->get('/faqs');
        $response->assertStatus(200);

        // Test complains page
        $response = $this->get('/complains');
        $response->assertStatus(200);

        // Test terms page
        $response = $this->get('/terms');
        $response->assertStatus(200);
    }

    /**
     * Test: Web Routes - Authentication Required
     */
    public function test_authenticated_web_routes_require_login(): void
    {
        // Test dashboard requires authentication
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');

        // Test questions index requires authentication
        $response = $this->get('/questions');
        $response->assertRedirect('/login');

        // Test solutions index requires authentication
        $response = $this->get('/solutions');
        $response->assertRedirect('/login');

        // Test home requires authentication
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }

    /**
     * Test: Web Routes - Authenticated Access
     */
    public function test_authenticated_web_routes_work_when_logged_in(): void
    {
        $this->actingAs($this->user);

        // Test dashboard
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        // Test questions index
        $response = $this->get('/questions');
        $response->assertStatus(200);

        // Test questions ask form
        $response = $this->get('/questions/ask');
        $response->assertStatus(200);

        // Test solutions index
        $response = $this->get('/solutions');
        $response->assertStatus(200);

        // Test solutions create form
        $response = $this->get('/solution/create');
        $response->assertStatus(200);

        // Test home
        $response = $this->get('/home');
        $response->assertStatus(200);
    }

    /**
     * Test: Questions Controller - Create Question
     */
    public function test_questions_controller_can_create_question(): void
    {
        $this->actingAs($this->user);

        $questionData = [
            'question' => 'How do I test Laravel routes?',
            'description' => 'I need help understanding how to test routes in Laravel 11.',
            'tags' => 'laravel,testing,routes',
        ];

        $response = $this->post('/questions/add', $questionData);
        $response->assertRedirect();

        $this->assertDatabaseHas('questions', [
            'user_id' => $this->user->id,
            'question' => 'How do I test Laravel routes?',
        ]);
    }

    /**
     * Test: Questions Controller - View Question
     */
    public function test_questions_controller_can_view_question(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/question/view/{$question->id}");
        $response->assertStatus(200);
        $response->assertSee($question->question);
    }

    /**
     * Test: Solutions Controller - Create Solution
     */
    public function test_solutions_controller_can_create_solution(): void
    {
        $this->actingAs($this->user);

        $solutionData = [
            'solution_title' => 'How to Test Routes',
            'solution_description' => 'A comprehensive guide to testing routes',
            'tags' => 'testing,routes',
            'duration' => 2,
            'duration_type' => 'hours',
            'solution_heading' => ['Step 1', 'Step 2'],
            'solution_body' => ['First step content', 'Second step content'],
        ];

        $response = $this->post('/solution/add', $solutionData);
        $response->assertRedirect();

        $this->assertDatabaseHas('solutions', [
            'user_id' => $this->user->id,
            'solution_title' => 'How to Test Routes',
        ]);
    }

    /**
     * Test: Solutions Controller - View Solution
     */
    public function test_solutions_controller_can_view_solution(): void
    {
        $this->actingAs($this->user);

        $solution = Solutions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Steps::factory()->count(2)->create([
            'solution_id' => $solution->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/solution/view/{$solution->id}");
        $response->assertStatus(200);
        $response->assertSee($solution->solution_title);
    }

    /**
     * Test: Answer Controller - Create Answer
     */
    public function test_answer_controller_can_create_answer(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $answerData = [
            'content' => 'This is my answer to the question.',
        ];

        $response = $this->post("/questions/{$question->id}/answers", $answerData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('answers', [
            'user_id' => $this->user->id,
            'question_id' => $question->id,
            'content' => 'This is my answer to the question.',
        ]);
    }

    /**
     * Test: Answer Controller - Delete Answer
     */
    public function test_answer_controller_can_delete_own_answer(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->delete("/answers/{$answer->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('answers', [
            'id' => $answer->id,
        ]);
    }

    /**
     * Test: Answer Controller - Toggle Like
     */
    public function test_answer_controller_can_toggle_like(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->otherUser->id,
            'question_id' => $question->id,
        ]);

        // Like the answer
        $response = $this->post("/answers/{$answer->id}/like", ['like' => true]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('likes', [
            'user_id' => $this->user->id,
            'likable_type' => Answer::class,
            'likable_id' => $answer->id,
            'like' => true,
        ]);
    }

    /**
     * Test: Answer Controller - Add Comment
     */
    public function test_answer_controller_can_add_comment(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->otherUser->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->otherUser->id,
            'question_id' => $question->id,
        ]);

        $commentData = [
            'body' => 'This is a great answer!',
        ];

        $response = $this->post("/answers/{$answer->id}/comments", $commentData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('comments', [
            'user_id' => $this->user->id,
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => 'This is a great answer!',
        ]);
    }

    /**
     * Test: Answer Controller - Toggle Acceptance
     */
    public function test_answer_controller_can_toggle_acceptance(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->otherUser->id,
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        // Accept the answer
        $response = $this->post("/answers/{$answer->id}/accept");
        $response->assertStatus(200);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => true,
        ]);
    }

    /**
     * Test: API Routes - User Endpoint
     */
    public function test_api_user_endpoint_requires_authentication(): void
    {
        // Without authentication
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);

        // With authentication
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user');
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * Test: API Routes - Answer CRUD
     */
    public function test_api_answer_crud_operations(): void
    {
        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create answer via API
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/answers', [
                'question_id' => $question->id,
                'content' => 'API answer content',
            ]);
        $response->assertStatus(201);
        $answerId = $response->json('data.id');

        // Get answers for question
        $response = $this->getJson("/api/questions/{$question->id}/answers");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'content', 'user_id', 'question_id'],
            ],
        ]);

        // Update answer via API
        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/answers/{$answerId}", [
                'content' => 'Updated API answer content',
            ]);
        $response->assertStatus(200);

        // Delete answer via API
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/answers/{$answerId}");
        $response->assertStatus(200);
    }

    /**
     * Test: API Routes - Rate Limiting
     */
    public function test_api_routes_have_rate_limiting(): void
    {
        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Make multiple requests to test rate limiting
        // Note: This is a basic check; actual rate limit testing would require more requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson("/api/questions/{$question->id}/answers");
            $response->assertStatus(200);
        }

        // Verify rate limit headers are present
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    }

    /**
     * Test: Eager Loading Optimization - Questions
     */
    public function test_questions_controller_uses_eager_loading(): void
    {
        $this->actingAs($this->user);

        // Create questions with relationships
        $questions = Questions::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        foreach ($questions as $question) {
            Answer::factory()->count(2)->create([
                'question_id' => $question->id,
                'user_id' => $this->otherUser->id,
            ]);
        }

        // Enable query logging
        DB::enableQueryLog();

        // Access questions index
        $response = $this->get('/questions');
        $response->assertStatus(200);

        // Get query log
        $queries = DB::getQueryLog();
        
        // Count queries - should be minimal due to eager loading
        // Typically: 1 for questions, 1 for users, 1 for answers
        $this->assertLessThan(10, count($queries), 
            'Too many queries detected. Eager loading may not be working properly.');

        DB::disableQueryLog();
    }

    /**
     * Test: Eager Loading Optimization - Solutions
     */
    public function test_solutions_controller_uses_eager_loading(): void
    {
        $this->actingAs($this->user);

        // Create solutions with relationships
        $solutions = Solutions::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        foreach ($solutions as $solution) {
            Steps::factory()->count(3)->create([
                'solution_id' => $solution->id,
                'user_id' => $this->user->id,
            ]);
        }

        // Enable query logging
        DB::enableQueryLog();

        // Access solutions index
        $response = $this->get('/solutions');
        $response->assertStatus(200);

        // Get query log
        $queries = DB::getQueryLog();
        
        // Count queries - should be minimal due to eager loading
        $this->assertLessThan(10, count($queries), 
            'Too many queries detected. Eager loading may not be working properly.');

        DB::disableQueryLog();
    }

    /**
     * Test: Eager Loading Optimization - Answers
     */
    public function test_answer_controller_uses_eager_loading(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create multiple answers with relationships
        Answer::factory()->count(5)->create([
            'question_id' => $question->id,
            'user_id' => $this->otherUser->id,
        ]);

        // Enable query logging
        DB::enableQueryLog();

        // Access answers for question
        $response = $this->get("/questions/{$question->id}/answers");
        $response->assertStatus(200);

        // Get query log
        $queries = DB::getQueryLog();
        
        // Count queries - should be minimal due to eager loading
        $this->assertLessThan(8, count($queries), 
            'Too many queries detected. Eager loading may not be working properly.');

        DB::disableQueryLog();
    }

    /**
     * Test: Pages Controller - Profile Routes
     */
    public function test_pages_controller_profile_routes(): void
    {
        $this->actingAs($this->user);

        // Test profile edit
        $response = $this->get('/user/profile/edit');
        $response->assertStatus(200);

        // Test profile show
        $response = $this->get("/user/profile/{$this->user->id}");
        $response->assertStatus(200);
    }

    /**
     * Test: Pages Controller - Contact Form
     */
    public function test_pages_controller_contact_form_submission(): void
    {
        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'This is a test message.',
        ];

        $response = $this->post('/contact-send', $contactData);
        
        // Should redirect or return success
        $this->assertTrue(
            $response->isRedirect() || $response->isSuccessful(),
            'Contact form submission should succeed or redirect'
        );
    }

    /**
     * Test: Authorization - Users Cannot Delete Others' Answers
     */
    public function test_users_cannot_delete_others_answers(): void
    {
        $this->actingAs($this->user);

        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->otherUser->id,
            'question_id' => $question->id,
        ]);

        $response = $this->delete("/answers/{$answer->id}");
        $response->assertStatus(403);

        // Answer should still exist
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
        ]);
    }

    /**
     * Test: Authorization - Only Question Owner Can Accept Answers
     */
    public function test_only_question_owner_can_accept_answers(): void
    {
        $this->actingAs($this->otherUser);

        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->otherUser->id,
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        // Try to accept answer as non-owner
        $response = $this->post("/answers/{$answer->id}/accept");
        $response->assertStatus(403);

        // Answer should not be accepted
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => false,
        ]);
    }

    /**
     * Test: API Resource Handler - Consistent Response Format
     */
    public function test_api_responses_have_consistent_format(): void
    {
        $question = Questions::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $this->user->id,
            'question_id' => $question->id,
        ]);

        // Test single resource response
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/answers/{$answer->id}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'content',
                'user_id',
                'question_id',
            ],
        ]);

        // Test collection response
        $response = $this->getJson("/api/questions/{$question->id}/answers");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'content', 'user_id', 'question_id'],
            ],
        ]);
    }
}
