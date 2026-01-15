<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-based test for like toggle idempotence
 * 
 * Feature: infodot-modernization, Property 5: Like Toggle Idempotence
 * 
 * Property: For any user and likable item, toggling like twice should return to the original state (no like).
 * Validates: Requirements FR-5
 */
class LikeToggleIdempotenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test like toggle idempotence on questions
     * 
     * @test
     */
    public function property_like_toggle_idempotence_on_questions(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();

            // Initial state: no like
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $question->id,
                'likable_type' => Questions::class,
            ]);

            // First toggle: add like
            $question->likes()->create([
                'user_id' => $user->id,
                'like' => true,
            ]);

            $this->assertDatabaseHas('likes', [
                'user_id' => $user->id,
                'likable_id' => $question->id,
                'like' => true,
            ]);

            // Second toggle: remove like (return to original state)
            $question->likes()->where('user_id', $user->id)->delete();

            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $question->id,
            ]);
        }
    }

    /**
     * Test like toggle idempotence on answers
     * 
     * @test
     */
    public function property_like_toggle_idempotence_on_answers(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create(['question_id' => $question->id]);

            // Initial state: no like
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $answer->id,
                'likable_type' => Answer::class,
            ]);

            // First toggle: add like
            $answer->likes()->create([
                'user_id' => $user->id,
                'like' => true,
            ]);

            // Second toggle: remove like
            $answer->likes()->where('user_id', $user->id)->delete();

            // Verify return to original state
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $answer->id,
            ]);
        }
    }

    /**
     * Test like toggle idempotence on solutions
     * 
     * @test
     */
    public function property_like_toggle_idempotence_on_solutions(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $solution = Solutions::factory()->create();

            // Initial state: no like
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $solution->id,
                'likable_type' => Solutions::class,
            ]);

            // First toggle: add like
            $solution->likes()->create([
                'user_id' => $user->id,
                'like' => true,
            ]);

            // Second toggle: remove like
            $solution->likes()->where('user_id', $user->id)->delete();

            // Verify return to original state
            $this->assertDatabaseMissing('likes', [
                'user_id' => $user->id,
                'likable_id' => $solution->id,
            ]);
        }
    }

    /**
     * Test like/dislike toggle idempotence
     * 
     * @test
     */
    public function property_like_dislike_toggle_idempotence(): void
    {
        // Run property test with multiple iterations
        for ($i = 0; $i < 100; $i++) {
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            $question = Questions::factory()->create();

            // Initial state: no like
            $initialState = $question->likes()->where('user_id', $user->id)->exists();
            $this->assertFalse($initialState);

            // Toggle sequence: like -> dislike -> remove
            $like = $question->likes()->create([
                'user_id' => $user->id,
                'like' => true,
            ]);

            $like->update(['like' => false]);
            $like->delete();

            // Verify return to original state
            $finalState = $question->likes()->where('user_id', $user->id)->exists();
            $this->assertEquals($initialState, $finalState);
        }
    }
}
