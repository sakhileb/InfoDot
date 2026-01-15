<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for answer management functionality
 * 
 * Tests answer creation, deletion, acceptance, likes/dislikes, and comments
 * Requirements: FR-3, FR-5, TR-1
 */
class AnswerManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated users can create an answer
     */
    public function test_authenticated_users_can_create_answer(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'This is my answer to the question.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('answers', [
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'This is my answer to the question.',
        ]);
    }

    /**
     * Test that answer creation requires authentication
     */
    public function test_answer_creation_requires_authentication(): void
    {
        $question = Questions::factory()->create();

        $response = $this->postJson('/api/answers', [
            'question_id' => $question->id,
            'content' => 'Test answer',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that answer creation validates required fields
     */
    public function test_answer_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/answers', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['question_id', 'content']);
    }

    /**
     * Test that users can delete their own answers
     */
    public function test_users_can_delete_own_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('answers', ['id' => $answer->id]);
    }

    /**
     * Test that users cannot delete others' answers
     */
    public function test_users_cannot_delete_others_answers(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user1->id]);
        $answer = Answer::factory()->create([
            'user_id' => $user1->id,
            'question_id' => $question->id,
        ]);

        $response = $this->actingAs($user2)->deleteJson("/api/answers/{$answer->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('answers', ['id' => $answer->id]);
    }

    /**
     * Test that question authors can accept answers
     */
    public function test_question_authors_can_accept_answers(): void
    {
        $questionAuthor = User::factory()->create();
        $answerAuthor = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        $answer = Answer::factory()->create([
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        $response = $this->actingAs($questionAuthor)
            ->postJson("/api/answers/{$answer->id}/toggle-acceptance");

        $response->assertStatus(200);
        $this->assertDatabaseHas('answers', [
            'id' => $answer->id,
            'is_accepted' => true,
        ]);
    }

    /**
     * Test that only one answer can be accepted per question
     */
    public function test_only_one_answer_can_be_accepted_per_question(): void
    {
        $questionAuthor = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        
        $answer1 = Answer::factory()->create([
            'question_id' => $question->id,
            'is_accepted' => true,
        ]);
        
        $answer2 = Answer::factory()->create([
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        // Accept answer2
        $this->actingAs($questionAuthor)
            ->postJson("/api/answers/{$answer2->id}/toggle-acceptance");

        // Verify answer1 is no longer accepted
        $this->assertDatabaseHas('answers', [
            'id' => $answer1->id,
            'is_accepted' => false,
        ]);

        // Verify answer2 is now accepted
        $this->assertDatabaseHas('answers', [
            'id' => $answer2->id,
            'is_accepted' => true,
        ]);
    }

    /**
     * Test that non-question-authors cannot accept answers
     */
    public function test_non_question_authors_cannot_accept_answers(): void
    {
        $questionAuthor = User::factory()->create();
        $otherUser = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'is_accepted' => false,
        ]);

        $response = $this->actingAs($otherUser)
            ->postJson("/api/answers/{$answer->id}/toggle-acceptance");

        $response->assertStatus(403);
    }

    /**
     * Test that users can like answers
     */
    public function test_users_can_like_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/toggle-like", ['like' => true]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likable_id' => $answer->id,
            'likable_type' => Answer::class,
            'like' => true,
        ]);
    }

    /**
     * Test that users can dislike answers
     */
    public function test_users_can_dislike_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/toggle-like", ['like' => false]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likable_id' => $answer->id,
            'likable_type' => Answer::class,
            'like' => false,
        ]);
    }

    /**
     * Test that users can toggle their like/dislike
     */
    public function test_users_can_toggle_like_dislike(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        // First like
        $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/toggle-like", ['like' => true]);

        // Then dislike
        $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/toggle-like", ['like' => false]);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'likable_id' => $answer->id,
            'like' => false,
        ]);
    }

    /**
     * Test that users can add comments to answers
     */
    public function test_users_can_add_comments_to_answers(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/answers/{$answer->id}/comments", [
                'body' => 'This is a helpful comment.',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_id' => $answer->id,
            'commentable_type' => Answer::class,
            'body' => 'This is a helpful comment.',
        ]);
    }

    /**
     * Test that users can retrieve comments for an answer
     */
    public function test_users_can_retrieve_answer_comments(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);
        
        // Create some comments
        $answer->comments()->create([
            'user_id' => $user->id,
            'body' => 'Comment 1',
        ]);
        $answer->comments()->create([
            'user_id' => $user->id,
            'body' => 'Comment 2',
        ]);

        $response = $this->actingAs($user)
            ->getJson("/api/answers/{$answer->id}/comments");

        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }
}
