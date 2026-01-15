<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Associates;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Questions;
use App\Models\Solutions;
use App\Models\Steps;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Checkpoint 6: Model Relationship Verification
 * 
 * This test verifies all model relationships work correctly:
 * - Test all model relationships
 * - Verify eager loading works
 * - Test polymorphic relationships
 * - Verify cascade deletes
 */
class ModelRelationshipCheckpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test User model relationships.
     *
     * @test
     */
    public function test_user_has_all_expected_relationships(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create related records
        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $solution = Solutions::create([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 1,
        ]);

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        Like::create([
            'user_id' => $user->id,
            'likable_type' => Questions::class,
            'likable_id' => $question->id,
            'like' => true,
        ]);

        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
            'body' => 'Test Comment',
        ]);

        Associates::create([
            'user_id' => $user->id,
            'associate_id' => $otherUser->id,
        ]);

        // Insert follower relationship
        DB::table('followers')->insert([
            'user_id' => $otherUser->id,
            'following_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Refresh user to load relationships
        $user = $user->fresh();

        // Verify relationships exist
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->questions);
        $this->assertCount(1, $user->questions);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->solutions);
        $this->assertCount(1, $user->solutions);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->answers);
        $this->assertCount(1, $user->answers);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->likes);
        $this->assertCount(1, $user->likes);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->comments);
        $this->assertCount(1, $user->comments);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->associates);
        $this->assertCount(1, $user->associates);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->followers);
        $this->assertCount(1, $user->followers);
    }

    /**
     * Test Question model relationships.
     *
     * @test
     */
    public function test_question_has_all_expected_relationships(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer 1',
        ]);

        Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer 2',
        ]);

        Like::create([
            'user_id' => $user->id,
            'likable_type' => Questions::class,
            'likable_id' => $question->id,
            'like' => true,
        ]);

        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
            'body' => 'Test Comment',
        ]);

        $question = $question->fresh();

        // Verify relationships
        $this->assertInstanceOf(User::class, $question->user);
        $this->assertEquals($user->id, $question->user->id);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $question->answers);
        $this->assertCount(2, $question->answers);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $question->likes);
        $this->assertCount(1, $question->likes);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $question->comments);
        $this->assertCount(1, $question->comments);
    }

    /**
     * Test Answer model relationships.
     *
     * @test
     */
    public function test_answer_has_all_expected_relationships(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        Like::create([
            'user_id' => $user->id,
            'likable_type' => Answer::class,
            'likable_id' => $answer->id,
            'like' => true,
        ]);

        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => 'Test Comment',
        ]);

        $answer = $answer->fresh();

        // Verify relationships
        $this->assertInstanceOf(User::class, $answer->user);
        $this->assertEquals($user->id, $answer->user->id);
        
        $this->assertInstanceOf(Questions::class, $answer->question);
        $this->assertEquals($question->id, $answer->question->id);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $answer->likes);
        $this->assertCount(1, $answer->likes);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $answer->comments);
        $this->assertCount(1, $answer->comments);
    }

    /**
     * Test Solution model relationships.
     *
     * @test
     */
    public function test_solution_has_all_expected_relationships(): void
    {
        $user = User::factory()->create();

        $solution = Solutions::create([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 2,
        ]);

        Steps::create([
            'user_id' => $user->id,
            'solution_id' => $solution->id,
            'solution_heading' => 'Step 1',
            'solution_body' => 'Body 1',
        ]);

        Steps::create([
            'user_id' => $user->id,
            'solution_id' => $solution->id,
            'solution_heading' => 'Step 2',
            'solution_body' => 'Body 2',
        ]);

        Like::create([
            'user_id' => $user->id,
            'likable_type' => Solutions::class,
            'likable_id' => $solution->id,
            'like' => true,
        ]);

        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Solutions::class,
            'commentable_id' => $solution->id,
            'body' => 'Test Comment',
        ]);

        $solution = $solution->fresh();

        // Verify relationships
        $this->assertInstanceOf(User::class, $solution->user);
        $this->assertEquals($user->id, $solution->user->id);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $solution->steps);
        $this->assertCount(2, $solution->steps);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $solution->likes);
        $this->assertCount(1, $solution->likes);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $solution->comments);
        $this->assertCount(1, $solution->comments);
    }

    /**
     * Test polymorphic Like relationships.
     *
     * @test
     */
    public function test_polymorphic_like_relationships_work(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        $solution = Solutions::create([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 1,
        ]);

        // Create likes for different models
        $questionLike = Like::create([
            'user_id' => $user->id,
            'likable_type' => Questions::class,
            'likable_id' => $question->id,
            'like' => true,
        ]);

        $answerLike = Like::create([
            'user_id' => $user->id,
            'likable_type' => Answer::class,
            'likable_id' => $answer->id,
            'like' => true,
        ]);

        $solutionLike = Like::create([
            'user_id' => $user->id,
            'likable_type' => Solutions::class,
            'likable_id' => $solution->id,
            'like' => true,
        ]);

        // Verify polymorphic relationships
        $this->assertInstanceOf(Questions::class, $questionLike->likable);
        $this->assertEquals($question->id, $questionLike->likable->id);
        
        $this->assertInstanceOf(Answer::class, $answerLike->likable);
        $this->assertEquals($answer->id, $answerLike->likable->id);
        
        $this->assertInstanceOf(Solutions::class, $solutionLike->likable);
        $this->assertEquals($solution->id, $solutionLike->likable->id);
    }

    /**
     * Test polymorphic Comment relationships.
     *
     * @test
     */
    public function test_polymorphic_comment_relationships_work(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        $solution = Solutions::create([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 1,
        ]);

        // Create comments for different models
        $questionComment = Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
            'body' => 'Question Comment',
        ]);

        $answerComment = Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => 'Answer Comment',
        ]);

        $solutionComment = Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Solutions::class,
            'commentable_id' => $solution->id,
            'body' => 'Solution Comment',
        ]);

        // Verify polymorphic relationships
        $this->assertInstanceOf(Questions::class, $questionComment->commentable);
        $this->assertEquals($question->id, $questionComment->commentable->id);
        
        $this->assertInstanceOf(Answer::class, $answerComment->commentable);
        $this->assertEquals($answer->id, $answerComment->commentable->id);
        
        $this->assertInstanceOf(Solutions::class, $solutionComment->commentable);
        $this->assertEquals($solution->id, $solutionComment->commentable->id);
    }

    /**
     * Test eager loading prevents N+1 queries.
     *
     * @test
     */
    public function test_eager_loading_prevents_n_plus_one_queries(): void
    {
        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            Questions::create([
                'user_id' => $user->id,
                'question' => "Question by {$user->name}",
                'description' => 'Test Description',
            ]);
        }

        // Enable query log
        DB::enableQueryLog();

        // Load questions without eager loading
        $questions = Questions::all();
        $queryCountWithoutEagerLoading = count(DB::getQueryLog());
        
        // Access user relationship (triggers N+1)
        foreach ($questions as $question) {
            $userName = $question->user->name;
        }
        
        $totalQueriesWithoutEagerLoading = count(DB::getQueryLog());

        // Clear query log
        DB::flushQueryLog();

        // Load questions with eager loading
        $questionsWithEagerLoading = Questions::with('user')->get();
        $queryCountWithEagerLoading = count(DB::getQueryLog());
        
        // Access user relationship (no additional queries)
        foreach ($questionsWithEagerLoading as $question) {
            $userName = $question->user->name;
        }
        
        $totalQueriesWithEagerLoading = count(DB::getQueryLog());

        // Verify eager loading reduces queries
        $this->assertLessThan($totalQueriesWithoutEagerLoading, $totalQueriesWithEagerLoading);
        $this->assertEquals($queryCountWithEagerLoading, $totalQueriesWithEagerLoading);
    }

    /**
     * Test nested eager loading works correctly.
     *
     * @test
     */
    public function test_nested_eager_loading_works(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Answer::class,
            'commentable_id' => $answer->id,
            'body' => 'Test Comment',
        ]);

        // Load question with nested relationships
        $loadedQuestion = Questions::with(['answers.comments.user'])->find($question->id);

        // Verify nested relationships are loaded
        $this->assertTrue($loadedQuestion->relationLoaded('answers'));
        $this->assertTrue($loadedQuestion->answers->first()->relationLoaded('comments'));
        $this->assertTrue($loadedQuestion->answers->first()->comments->first()->relationLoaded('user'));
    }

    /**
     * Test cascade delete for question and its answers.
     *
     * @test
     */
    public function test_cascade_delete_question_removes_answers(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $answer1 = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer 1',
        ]);

        $answer2 = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'content' => 'Test Answer 2',
        ]);

        // Delete question
        $question->delete();

        // Verify answers are deleted
        $this->assertDatabaseMissing('answers', ['id' => $answer1->id]);
        $this->assertDatabaseMissing('answers', ['id' => $answer2->id]);
    }

    /**
     * Test cascade delete for solution and its steps.
     *
     * @test
     */
    public function test_cascade_delete_solution_removes_steps(): void
    {
        $user = User::factory()->create();

        $solution = Solutions::create([
            'user_id' => $user->id,
            'solution_title' => 'Test Solution',
            'solution_description' => 'Test Description',
            'tags' => 'test',
            'duration' => 1,
            'duration_type' => 'hours',
            'steps' => 2,
        ]);

        $step1 = Steps::create([
            'user_id' => $user->id,
            'solution_id' => $solution->id,
            'solution_heading' => 'Step 1',
            'solution_body' => 'Body 1',
        ]);

        $step2 = Steps::create([
            'user_id' => $user->id,
            'solution_id' => $solution->id,
            'solution_heading' => 'Step 2',
            'solution_body' => 'Body 2',
        ]);

        // Delete solution
        $solution->delete();

        // Verify steps are deleted
        $this->assertDatabaseMissing('solutions_step', ['id' => $step1->id]);
        $this->assertDatabaseMissing('solutions_step', ['id' => $step2->id]);
    }

    /**
     * Test nested comment relationships (parent-child).
     *
     * @test
     */
    public function test_nested_comment_relationships_work(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $parentComment = Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
            'body' => 'Parent Comment',
        ]);

        $childComment = Comment::create([
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'commentable_type' => Questions::class,
            'commentable_id' => $question->id,
            'body' => 'Child Comment',
        ]);

        $parentComment = $parentComment->fresh();

        // Verify parent-child relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $parentComment->children);
        $this->assertCount(1, $parentComment->children);
        $this->assertEquals($childComment->id, $parentComment->children->first()->id);
    }

    /**
     * Test nested like relationships (parent-child).
     *
     * @test
     */
    public function test_nested_like_relationships_work(): void
    {
        $user = User::factory()->create();

        $question = Questions::create([
            'user_id' => $user->id,
            'question' => 'Test Question',
            'description' => 'Test Description',
        ]);

        $parentLike = Like::create([
            'user_id' => $user->id,
            'likable_type' => Questions::class,
            'likable_id' => $question->id,
            'like' => true,
        ]);

        $childLike = Like::create([
            'user_id' => $user->id,
            'parent_id' => $parentLike->id,
            'likable_type' => Questions::class,
            'likable_id' => $question->id,
            'like' => true,
        ]);

        $parentLike = $parentLike->fresh();

        // Verify parent-child relationship
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $parentLike->children);
        $this->assertCount(1, $parentLike->children);
        $this->assertEquals($childLike->id, $parentLike->children->first()->id);
    }

    /**
     * Test follower relationships (self-referential many-to-many).
     *
     * @test
     */
    public function test_follower_relationships_work(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // User2 and User3 follow User1
        DB::table('followers')->insert([
            ['user_id' => $user2->id, 'following_id' => $user1->id, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $user3->id, 'following_id' => $user1->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // User1 follows User2
        DB::table('followers')->insert([
            ['user_id' => $user1->id, 'following_id' => $user2->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $user1 = $user1->fresh();
        $user2 = $user2->fresh();

        // Verify User1 has 2 followers
        $this->assertCount(2, $user1->followers);
        
        // Verify User1 is following 1 user
        $this->assertCount(1, $user1->following);
        
        // Verify User2 has 1 follower (User1)
        $this->assertCount(1, $user2->followers);
    }

    /**
     * Test associate relationships.
     *
     * @test
     */
    public function test_associate_relationships_work(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        Associates::create([
            'user_id' => $user1->id,
            'associate_id' => $user2->id,
        ]);

        Associates::create([
            'user_id' => $user1->id,
            'associate_id' => $user3->id,
        ]);

        $user1 = $user1->fresh();

        // Verify User1 has 2 associates
        $this->assertCount(2, $user1->associates);
        
        // Verify associate IDs
        $associateIds = $user1->associates->pluck('associate_id')->toArray();
        $this->assertContains($user2->id, $associateIds);
        $this->assertContains($user3->id, $associateIds);
    }
}
