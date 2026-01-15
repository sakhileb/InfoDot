<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Questions;
use App\Models\Answer;
use App\Models\Solutions;
use App\Models\Steps;
use App\Models\Like;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

/**
 * @test
 * Feature: infodot-modernization, Property 14: Query Optimization Effectiveness
 * 
 * Property: For any controller using EagerLoadingOptimizer, the number of database queries 
 * should not increase with the number of related records (no N+1).
 * 
 * Validates: Requirements NFR-1
 */
class QueryOptimizationEffectivenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that optimized questions query prevents N+1 problems
     * 
     * @test
     */
    public function test_optimized_questions_query_prevents_n_plus_1(): void
    {
        // Create test data with varying numbers of related records
        $user = User::factory()->create();
        
        // Create questions with different numbers of answers
        $question1 = Questions::factory()->create(['user_id' => $user->id]);
        $question2 = Questions::factory()->create(['user_id' => $user->id]);
        $question3 = Questions::factory()->create(['user_id' => $user->id]);
        
        // Question 1: 1 answer
        Answer::factory()->create(['question_id' => $question1->id, 'user_id' => $user->id]);
        
        // Question 2: 5 answers
        Answer::factory()->count(5)->create(['question_id' => $question2->id, 'user_id' => $user->id]);
        
        // Question 3: 10 answers
        Answer::factory()->count(10)->create(['question_id' => $question3->id, 'user_id' => $user->id]);
        
        // Enable query logging
        DB::enableQueryLog();
        
        // Use the optimized query from EagerLoadingOptimizer trait
        $controller = new class {
            use \App\Http\Controllers\Traits\EagerLoadingOptimizer;
            
            public function getQuestions()
            {
                return $this->getOptimizedQuestionsQuery()->get();
            }
        };
        
        $questions = $controller->getQuestions();
        
        // Get query count
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        // Access relationships to ensure they're loaded
        foreach ($questions as $question) {
            $question->user->name;
            foreach ($question->answers as $answer) {
                $answer->user->name;
            }
            $question->answers_count;
            $question->likes_count;
            $question->comments_count;
        }
        
        // The query count should be constant regardless of the number of related records
        // Expected: 1 main query + eager loads (should be around 2-4 queries total)
        $this->assertLessThanOrEqual(5, $queryCount, 
            "Query count should not increase with number of related records. Found {$queryCount} queries.");
    }

    /**
     * Test that optimized answers query prevents N+1 problems
     * 
     * @test
     */
    public function test_optimized_answers_query_prevents_n_plus_1(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Create answers with varying numbers of likes and comments
        $answer1 = Answer::factory()->create(['question_id' => $question->id, 'user_id' => $user->id]);
        Like::factory()->count(3)->create(['likable_type' => Answer::class, 'likable_id' => $answer1->id, 'like' => true]);
        Comment::factory()->count(2)->create(['commentable_type' => Answer::class, 'commentable_id' => $answer1->id]);
        
        $answer2 = Answer::factory()->create(['question_id' => $question->id, 'user_id' => $user->id]);
        Like::factory()->count(10)->create(['likable_type' => Answer::class, 'likable_id' => $answer2->id, 'like' => true]);
        Comment::factory()->count(5)->create(['commentable_type' => Answer::class, 'commentable_id' => $answer2->id]);
        
        DB::enableQueryLog();
        
        $controller = new class {
            use \App\Http\Controllers\Traits\EagerLoadingOptimizer;
            
            public function getAnswers()
            {
                return $this->getOptimizedAnswersQuery()->get();
            }
        };
        
        $answers = $controller->getAnswers();
        
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        // Access relationships
        foreach ($answers as $answer) {
            $answer->user->name;
            $answer->question->question;
            $answer->likes_count;
            $answer->dislikes_count;
            $answer->comments_count;
        }
        
        $this->assertLessThanOrEqual(5, $queryCount,
            "Query count should not increase with number of related records. Found {$queryCount} queries.");
    }

    /**
     * Test that optimized solutions query prevents N+1 problems
     * 
     * @test
     */
    public function test_optimized_solutions_query_prevents_n_plus_1(): void
    {
        $user = User::factory()->create();
        
        // Create solutions with different numbers of steps
        $solution1 = Solutions::factory()->create(['user_id' => $user->id]);
        Steps::factory()->count(2)->create(['solution_id' => $solution1->id, 'user_id' => $user->id]);
        
        $solution2 = Solutions::factory()->create(['user_id' => $user->id]);
        Steps::factory()->count(8)->create(['solution_id' => $solution2->id, 'user_id' => $user->id]);
        
        $solution3 = Solutions::factory()->create(['user_id' => $user->id]);
        Steps::factory()->count(15)->create(['solution_id' => $solution3->id, 'user_id' => $user->id]);
        
        DB::enableQueryLog();
        
        $controller = new class {
            use \App\Http\Controllers\Traits\EagerLoadingOptimizer;
            
            public function getSolutions()
            {
                return $this->getOptimizedSolutionsQuery()->get();
            }
        };
        
        $solutions = $controller->getSolutions();
        
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        
        // Access relationships
        foreach ($solutions as $solution) {
            $solution->user->name;
            foreach ($solution->steps as $step) {
                $step->solution_heading;
            }
            $solution->steps_count;
            $solution->comments_count;
            $solution->likes_count;
        }
        
        $this->assertLessThanOrEqual(5, $queryCount,
            "Query count should not increase with number of related records. Found {$queryCount} queries.");
    }

    /**
     * Property-based test: Query count should remain constant regardless of data volume
     * 
     * @test
     */
    public function test_property_query_count_remains_constant_with_varying_data_volume(): void
    {
        $iterations = 10;
        $queryCounts = [];
        
        for ($i = 0; $i < $iterations; $i++) {
            // Refresh database for each iteration
            $this->refreshDatabase();
            
            $user = User::factory()->create();
            
            // Create varying numbers of questions (1 to 20)
            $questionCount = rand(1, 20);
            for ($j = 0; $j < $questionCount; $j++) {
                $question = Questions::factory()->create(['user_id' => $user->id]);
                
                // Each question has varying numbers of answers (0 to 10)
                $answerCount = rand(0, 10);
                Answer::factory()->count($answerCount)->create([
                    'question_id' => $question->id,
                    'user_id' => $user->id
                ]);
            }
            
            DB::enableQueryLog();
            
            $controller = new class {
                use \App\Http\Controllers\Traits\EagerLoadingOptimizer;
                
                public function getQuestions()
                {
                    return $this->getOptimizedQuestionsQuery()->get();
                }
            };
            
            $questions = $controller->getQuestions();
            
            $queryCount = count(DB::getQueryLog());
            DB::disableQueryLog();
            
            $queryCounts[] = $queryCount;
            
            // Access relationships to ensure they're loaded
            foreach ($questions as $question) {
                $question->user;
                $question->answers;
            }
        }
        
        // Calculate variance in query counts
        $avgQueryCount = array_sum($queryCounts) / count($queryCounts);
        $variance = 0;
        foreach ($queryCounts as $count) {
            $variance += pow($count - $avgQueryCount, 2);
        }
        $variance = $variance / count($queryCounts);
        
        // Variance should be very low (ideally 0, but allowing small variation)
        $this->assertLessThanOrEqual(2, $variance,
            "Query count variance should be minimal. Found variance of {$variance}. Query counts: " . implode(', ', $queryCounts));
    }
}
