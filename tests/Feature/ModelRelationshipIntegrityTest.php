<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Property 18: Model Relationship Integrity
 * 
 * For any model with relationships, deleting a parent record should properly
 * handle cascading deletes or prevent deletion.
 * 
 * Validates: Requirements DR-1 through DR-6
 * 
 * Feature: infodot-modernization, Property 18: Model Relationship Integrity
 */
class ModelRelationshipIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that deleting a user cascades to their questions.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_questions(): void
    {
        $user = User::factory()->create();
        
        // Create a question for the user
        DB::table('questions')->insert([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $questionId = DB::table('questions')->where('user_id', $user->id)->first()->id;
        
        // Delete the user
        $user->delete();
        
        // Verify the question was also deleted (cascade)
        $this->assertDatabaseMissing('questions', ['id' => $questionId]);
    }

    /**
     * Test that deleting a question cascades to its answers.
     *
     * @test
     */
    public function property_question_deletion_cascades_to_answers(): void
    {
        $user = User::factory()->create();
        
        // Create a question
        $questionId = DB::table('questions')->insertGetId([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create an answer for the question
        $answerId = DB::table('answers')->insertGetId([
            'user_id' => $user->id,
            'question_id' => $questionId,
            'content' => 'Test Answer',
            'is_accepted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the question
        DB::table('questions')->where('id', $questionId)->delete();
        
        // Verify the answer was also deleted (cascade)
        $this->assertDatabaseMissing('answers', ['id' => $answerId]);
    }

    /**
     * Test that deleting a user cascades to their solutions.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_solutions(): void
    {
        $user = User::factory()->create();
        
        // Create a solution for the user
        $solutionId = DB::table('solutions')->insertGetId([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the user
        $user->delete();
        
        // Verify the solution was also deleted (cascade)
        $this->assertDatabaseMissing('solutions', ['id' => $solutionId]);
    }

    /**
     * Test that deleting a solution cascades to its steps.
     *
     * @test
     */
    public function property_solution_deletion_cascades_to_steps(): void
    {
        $user = User::factory()->create();
        
        // Create a solution
        $solutionId = DB::table('solutions')->insertGetId([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a step for the solution
        $stepId = DB::table('solutions_step')->insertGetId([
            'user_id' => $user->id,
            'solution_id' => $solutionId,
            'solution_heading' => 'Test Step',
            'solution_body' => 'Test Body',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the solution
        DB::table('solutions')->where('id', $solutionId)->delete();
        
        // Verify the step was also deleted (cascade)
        $this->assertDatabaseMissing('solutions_step', ['id' => $stepId]);
    }

    /**
     * Test that deleting a user cascades to their comments.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_comments(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Create a question
        $questionId = DB::table('questions')->insertGetId([
            'user_id' => $otherUser->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a comment by the user
        $commentId = DB::table('comments')->insertGetId([
            'user_id' => $user->id,
            'body' => 'Test Comment',
            'commentable_type' => 'App\\Models\\Questions',
            'commentable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the user
        $user->delete();
        
        // Verify the comment was also deleted (cascade)
        $this->assertDatabaseMissing('comments', ['id' => $commentId]);
    }

    /**
     * Test that deleting a user cascades to their likes.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_likes(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        // Create a question
        $questionId = DB::table('questions')->insertGetId([
            'user_id' => $otherUser->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a like by the user
        $likeId = DB::table('likes')->insertGetId([
            'user_id' => $user->id,
            'like' => true,
            'likable_type' => 'App\\Models\\Questions',
            'likable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the user
        $user->delete();
        
        // Verify the like was also deleted (cascade)
        $this->assertDatabaseMissing('likes', ['id' => $likeId]);
    }

    /**
     * Test that deleting a user cascades to their follower relationships.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_followers(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create();
        
        // Create a follower relationship
        DB::table('followers')->insert([
            'user_id' => $follower->id,
            'following_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the user
        $user->delete();
        
        // Verify the follower relationship was also deleted (cascade)
        $this->assertDatabaseMissing('followers', [
            'following_id' => $user->id,
        ]);
    }

    /**
     * Test that deleting a user cascades to their associate relationships.
     *
     * @test
     */
    public function property_user_deletion_cascades_to_associates(): void
    {
        $user = User::factory()->create();
        $associate = User::factory()->create();
        
        // Create an associate relationship
        $associateId = DB::table('associates')->insertGetId([
            'user_id' => $user->id,
            'associate_id' => $associate->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the user
        $user->delete();
        
        // Verify the associate relationship was also deleted (cascade)
        $this->assertDatabaseMissing('associates', ['id' => $associateId]);
    }

    /**
     * Test that parent comment deletion cascades to child comments.
     *
     * @test
     */
    public function property_parent_comment_deletion_cascades_to_children(): void
    {
        $user = User::factory()->create();
        
        // Create a question
        $questionId = DB::table('questions')->insertGetId([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a parent comment
        $parentCommentId = DB::table('comments')->insertGetId([
            'user_id' => $user->id,
            'body' => 'Parent Comment',
            'commentable_type' => 'App\\Models\\Questions',
            'commentable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a child comment
        $childCommentId = DB::table('comments')->insertGetId([
            'user_id' => $user->id,
            'parent_id' => $parentCommentId,
            'body' => 'Child Comment',
            'commentable_type' => 'App\\Models\\Questions',
            'commentable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the parent comment
        DB::table('comments')->where('id', $parentCommentId)->delete();
        
        // Verify the child comment was also deleted (cascade)
        $this->assertDatabaseMissing('comments', ['id' => $childCommentId]);
    }

    /**
     * Test that parent like deletion cascades to child likes.
     *
     * @test
     */
    public function property_parent_like_deletion_cascades_to_children(): void
    {
        $user = User::factory()->create();
        
        // Create a question
        $questionId = DB::table('questions')->insertGetId([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a parent like
        $parentLikeId = DB::table('likes')->insertGetId([
            'user_id' => $user->id,
            'like' => true,
            'likable_type' => 'App\\Models\\Questions',
            'likable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a child like
        $childLikeId = DB::table('likes')->insertGetId([
            'user_id' => $user->id,
            'parent_id' => $parentLikeId,
            'like' => true,
            'likable_type' => 'App\\Models\\Questions',
            'likable_id' => $questionId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Delete the parent like
        DB::table('likes')->where('id', $parentLikeId)->delete();
        
        // Verify the child like was also deleted (cascade)
        $this->assertDatabaseMissing('likes', ['id' => $childLikeId]);
    }
}
