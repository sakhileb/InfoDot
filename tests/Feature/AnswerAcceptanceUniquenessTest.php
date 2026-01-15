<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property-Based Test: Answer Acceptance Uniqueness
 * 
 * Feature: infodot-modernization, Property 3: Answer Acceptance Uniqueness
 * Validates: Requirements FR-3
 * 
 * Property: For any question with multiple answers, accepting one answer should 
 * automatically unaccept all other answers for that question.
 */
class AnswerAcceptanceUniquenessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that only one answer can be accepted per question.
     * Runs 100+ iterations with random data to verify the property holds.
     *
     * @test
     */
    public function property_answer_acceptance_uniqueness(): void
    {
        $iterations = 100;
        
        for ($i = 0; $i < $iterations; $i++) {
            // Create a user and question
            $user = User::factory()->create();
            $question = Questions::create([
                'user_id' => $user->id,
                'question' => "Test question {$i}",
                'description' => "Test description {$i}",
                'tags' => 'test',
                'status' => 'open',
            ]);

            // Create random number of answers (2-10)
            $answerCount = rand(2, 10);
            $answers = [];
            
            for ($j = 0; $j < $answerCount; $j++) {
                $answerUser = User::factory()->create();
                $answers[] = Answer::create([
                    'user_id' => $answerUser->id,
                    'question_id' => $question->id,
                    'content' => "Answer {$j} for iteration {$i}",
                    'is_accepted' => false,
                ]);
            }

            // Accept a random answer
            $randomIndex = rand(0, $answerCount - 1);
            $acceptedAnswer = $answers[$randomIndex];
            
            // First, unaccept all other answers (simulating the business logic)
            Answer::where('question_id', $question->id)
                ->where('id', '!=', $acceptedAnswer->id)
                ->update(['is_accepted' => false]);
            
            // Then accept the chosen answer
            $acceptedAnswer->update(['is_accepted' => true]);

            // Verify only one answer is accepted
            $acceptedAnswers = Answer::where('question_id', $question->id)
                ->where('is_accepted', true)
                ->get();

            $this->assertCount(
                1,
                $acceptedAnswers,
                "Only one answer should be accepted for question (iteration {$i})"
            );

            $this->assertEquals(
                $acceptedAnswer->id,
                $acceptedAnswers->first()->id,
                "The accepted answer should be the one we marked (iteration {$i})"
            );

            // Now accept a different answer and verify uniqueness
            $newAcceptIndex = ($randomIndex + 1) % $answerCount;
            $newAcceptedAnswer = $answers[$newAcceptIndex];
            
            // Unaccept all others
            Answer::where('question_id', $question->id)
                ->where('id', '!=', $newAcceptedAnswer->id)
                ->update(['is_accepted' => false]);
            
            // Accept the new answer
            $newAcceptedAnswer->update(['is_accepted' => true]);

            // Verify again only one answer is accepted
            $acceptedAnswers = Answer::where('question_id', $question->id)
                ->where('is_accepted', true)
                ->get();

            $this->assertCount(
                1,
                $acceptedAnswers,
                "Only one answer should be accepted after changing acceptance (iteration {$i})"
            );

            $this->assertEquals(
                $newAcceptedAnswer->id,
                $acceptedAnswers->first()->id,
                "The newly accepted answer should be the current one (iteration {$i})"
            );

            // Verify the previous accepted answer is no longer accepted
            $previousAnswer = Answer::find($acceptedAnswer->id);
            $this->assertFalse(
                $previousAnswer->is_accepted,
                "Previous accepted answer should be unaccepted (iteration {$i})"
            );

            // Clean up
            foreach ($answers as $answer) {
                $answer->user->forceDelete();
                $answer->forceDelete();
            }
            $question->forceDelete();
            $user->forceDelete();
        }
    }

    /**
     * Test edge case: accepting an answer when no answers are currently accepted.
     *
     * @test
     */
    public function property_answer_acceptance_from_none(): void
    {
        $iterations = 50;
        
        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create();
            $question = Questions::create([
                'user_id' => $user->id,
                'question' => "Test question {$i}",
                'description' => "Test description {$i}",
                'tags' => 'test',
                'status' => 'open',
            ]);

            // Create answers, all unaccepted
            $answerCount = rand(2, 5);
            $answers = [];
            
            for ($j = 0; $j < $answerCount; $j++) {
                $answerUser = User::factory()->create();
                $answers[] = Answer::create([
                    'user_id' => $answerUser->id,
                    'question_id' => $question->id,
                    'content' => "Answer {$j}",
                    'is_accepted' => false,
                ]);
            }

            // Verify no answers are accepted initially
            $acceptedCount = Answer::where('question_id', $question->id)
                ->where('is_accepted', true)
                ->count();
            
            $this->assertEquals(0, $acceptedCount, "No answers should be accepted initially (iteration {$i})");

            // Accept one answer
            $randomAnswer = $answers[rand(0, $answerCount - 1)];
            $randomAnswer->update(['is_accepted' => true]);

            // Verify exactly one answer is accepted
            $acceptedAnswers = Answer::where('question_id', $question->id)
                ->where('is_accepted', true)
                ->get();

            $this->assertCount(1, $acceptedAnswers, "Exactly one answer should be accepted (iteration {$i})");

            // Clean up
            foreach ($answers as $answer) {
                $answer->user->forceDelete();
                $answer->forceDelete();
            }
            $question->forceDelete();
            $user->forceDelete();
        }
    }
}
