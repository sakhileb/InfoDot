<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test listing answers (GET /api/answers)
     */
    public function test_list_answers(): void
    {
        Answer::factory()->count(5)->create();

        $response = $this->getJson('/api/answers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'is_accepted',
                            'created_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test showing single answer (GET /api/answers/{id})
     */
    public function test_show_answer(): void
    {
        $answer = Answer::factory()->create();

        $response = $this->getJson("/api/answers/{$answer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $answer->id,
                    'content' => $answer->content,
                ],
            ]);
    }

    /**
     * Test creating answer requires authentication
     */
    public function test_create_answer_requires_authentication(): void
    {
        $question = Questions::factory()->create();

        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'Test answer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test creating answer with authentication
     */
    public function test_create_answer_with_authentication(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'Test answer content',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Answer created successfully',
                'data' => [
                    'content' => 'Test answer content',
                ],
            ]);

        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test answer content',
        ]);
    }

    /**
     * Test creating answer with invalid data
     */
    public function test_create_answer_with_invalid_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/answers', [
            'question_id' => 999999, // Non-existent question
            'content' => '',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test updating answer requires ownership
     */
    public function test_update_answer_requires_ownership(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->putJson("/api/answers/{$answer->id}", [
            'content' => 'Updated content',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test updating answer with ownership
     */
    public function test_update_answer_with_ownership(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/answers/{$answer->id}", [
            'content' => 'Updated answer content',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Answer updated successfully',
            ]);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'content' => 'Updated answer content',
        ]);
    }

    /**
     * Test deleting answer requires ownership
     */
    public function test_delete_answer_requires_ownership(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(403);
    }

    /**
     * Test deleting answer with ownership
     */
    public function test_delete_answer_with_ownership(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Answer deleted successfully',
            ]);

        $this->assertDatabaseMissing('answers', [
            'id' => $answer->id,
        ]);
    }

    /**
     * Test getting answers for a question
     */
    public function test_get_answers_for_question(): void
    {
        $question = Questions::factory()->create();
        Answer::factory()->count(3)->create(['question_id' => $question->id]);

        $response = $this->getJson("/api/questions/{$question->id}/answers");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'content',
                            'is_accepted',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test liking an answer
     */
    public function test_like_answer(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/like", [
            'like' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'action',
                    'likes_count',
                    'dislikes_count',
                ],
            ]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likable_type' => Answer::class,
            'likable_id' => $answer->id,
            'like' => true,
        ]);
    }

    /**
     * Test disliking an answer
     */
    public function test_dislike_answer(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/like", [
            'like' => false,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likable_type' => Answer::class,
            'likable_id' => $answer->id,
            'like' => false,
        ]);
    }

    /**
     * Test adding comment to answer
     */
    public function test_add_comment_to_answer(): void
    {
        $user = User::factory()->create();
        $answer = Answer::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/comments", [
            'body' => 'This is a test comment',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Comment added successfully',
            ]);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => 'This is a test comment',
        ]);
    }

    /**
     * Test getting comments for answer
     */
    public function test_get_comments_for_answer(): void
    {
        $answer = Answer::factory()->create();
        Comment::factory()->count(3)->create([
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson("/api/answers/{$answer->id}/comments");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'body',
                            'created_at',
                        ],
                    ],
                ],
            ]);
    }

    /**
     * Test accepting answer as question author
     */
    public function test_accept_answer_as_question_author(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/answers/{$answer->id}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_accepted' => true,
                ],
            ]);

        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => true,
        ]);
    }

    /**
     * Test accepting answer as non-question author fails
     */
    public function test_accept_answer_as_non_question_author_fails(): void
    {
        $questionAuthor = User::factory()->create();
        $otherUser = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/answers/{$answer->id}/accept");

        $response->assertStatus(403);
    }

    /**
     * Test rate limiting on API endpoints
     */
    public function test_rate_limiting_on_api_endpoints(): void
    {
        $answer = Answer::factory()->create();

        // Make requests up to the limit (60 per minute)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson("/api/answers/{$answer->id}");
            
            if ($i < 60) {
                $this->assertNotEquals(429, $response->status(), 
                    "Request {$i} should not be rate limited");
            } else {
                $this->assertEquals(429, $response->status(), 
                    "Request {$i} should be rate limited");
            }
        }
    }
}
