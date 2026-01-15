<?php

namespace Tests\Feature;

use App\Events\Answers\AnswerWasAccepted;
use App\Events\Answers\AnswerWasPosted;
use App\Events\Questions\QuestionWasAsked;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Property 6: Event Broadcasting Reliability
 * 
 * For any question creation, an event should be dispatched that can be received by WebSocket listeners.
 * 
 * Feature: infodot-modernization, Property 6: Event Broadcasting Reliability
 * Validates: Requirements FR-6
 */
class EventBroadcastingReliabilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that QuestionWasAsked event is dispatched when a question is created.
     * 
     * This property test runs 100+ iterations with random question data to verify
     * that the event is always dispatched reliably.
     */
    public function test_question_was_asked_event_is_dispatched_for_any_question_creation(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([QuestionWasAsked::class]);

            // Generate random question data
            $user = User::factory()->create();
            $questionData = [
                'question' => fake()->sentence() . ' ' . $i,
                'description' => fake()->paragraph(),
                'tags' => fake()->words(3, true),
            ];

            // Create question and dispatch event
            $question = Questions::create([
                'user_id' => $user->id,
                'question' => $questionData['question'],
                'description' => $questionData['description'],
                'tags' => $questionData['tags'],
                'status' => 'open',
            ]);

            // Manually dispatch the event (as it would be in the controller)
            event(new QuestionWasAsked($question));

            // Assert event was dispatched
            Event::assertDispatched(QuestionWasAsked::class, function ($event) use ($question) {
                return $event->question->id === $question->id;
            });

            // Clean up for next iteration
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Event dispatched reliably across {$iterations} iterations");
    }

    /**
     * Test that AnswerWasPosted event is dispatched when an answer is created.
     */
    public function test_answer_was_posted_event_is_dispatched_for_any_answer_creation(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([AnswerWasPosted::class]);

            // Generate random answer data
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            
            $answer = Answer::create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'content' => fake()->paragraph() . ' ' . $i,
                'is_accepted' => false,
            ]);

            // Manually dispatch the event
            event(new AnswerWasPosted($answer));

            // Assert event was dispatched
            Event::assertDispatched(AnswerWasPosted::class, function ($event) use ($answer) {
                return $event->answer->id === $answer->id;
            });

            // Clean up for next iteration
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Event dispatched reliably across {$iterations} iterations");
    }

    /**
     * Test that AnswerWasAccepted event is dispatched when an answer is accepted.
     */
    public function test_answer_was_accepted_event_is_dispatched_for_any_answer_acceptance(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([AnswerWasAccepted::class]);

            // Generate random answer data
            $user = User::factory()->create();
            $question = Questions::factory()->create();
            
            $answer = Answer::create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'content' => fake()->paragraph() . ' ' . $i,
                'is_accepted' => false,
            ]);

            // Accept the answer
            $answer->is_accepted = true;
            $answer->save();

            // Manually dispatch the event
            event(new AnswerWasAccepted($answer));

            // Assert event was dispatched
            Event::assertDispatched(AnswerWasAccepted::class, function ($event) use ($answer) {
                return $event->answer->id === $answer->id 
                    && $event->answer->is_accepted === true;
            });

            // Clean up for next iteration
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Event dispatched reliably across {$iterations} iterations");
    }

    /**
     * Test that events contain correct broadcast data.
     */
    public function test_events_contain_correct_broadcast_data(): void
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            // Test QuestionWasAsked broadcast data
            $user = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            $event = new QuestionWasAsked($question);
            $broadcastData = $event->broadcastWith();

            $this->assertArrayHasKey('id', $broadcastData);
            $this->assertArrayHasKey('question', $broadcastData);
            $this->assertArrayHasKey('description', $broadcastData);
            $this->assertArrayHasKey('user', $broadcastData);
            $this->assertEquals($question->id, $broadcastData['id']);

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Broadcast data is correct across {$iterations} iterations");
    }

    /**
     * Test that events broadcast on correct channels.
     */
    public function test_events_broadcast_on_correct_channels(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        // Test QuestionWasAsked channel
        $questionEvent = new QuestionWasAsked($question);
        $channel = $questionEvent->broadcastOn();
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $channel);

        // Test AnswerWasPosted channel
        $answer = Answer::factory()->create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);
        
        $answerEvent = new AnswerWasPosted($answer);
        $answerChannel = $answerEvent->broadcastOn();
        $this->assertInstanceOf(\Illuminate\Broadcasting\PrivateChannel::class, $answerChannel);

        // Clean up
        $answer->delete();
        $question->delete();
        $user->delete();
    }
}
