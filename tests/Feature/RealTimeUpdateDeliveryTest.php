<?php

namespace Tests\Feature;

use App\Events\Answers\AnswerWasPosted;
use App\Events\Questions\QuestionWasAsked;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Property 19: Real-time Update Delivery
 * 
 * For any user action that triggers a real-time update, connected clients should receive 
 * the update within 2 seconds.
 * 
 * Feature: infodot-modernization, Property 19: Real-time Update Delivery
 * Validates: Requirements UIR-3
 */
class RealTimeUpdateDeliveryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that events are queued for broadcasting without delay.
     * 
     * This property test verifies that events are dispatched immediately
     * and can be broadcast to connected clients. We test the dispatch
     * mechanism rather than actual WebSocket delivery (which requires
     * a running WebSocket server).
     */
    public function test_question_events_are_dispatched_immediately_for_broadcasting(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([QuestionWasAsked::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $user->id,
                'question' => 'Test question ' . $i,
            ]);

            $startTime = microtime(true);
            
            // Dispatch event
            event(new QuestionWasAsked($question));
            
            $endTime = microtime(true);
            $dispatchTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            // Assert event was dispatched
            Event::assertDispatched(QuestionWasAsked::class);

            // Assert dispatch happened quickly (under 100ms for local dispatch)
            $this->assertLessThan(100, $dispatchTime, 
                "Event dispatch took {$dispatchTime}ms, should be under 100ms");

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Events dispatched immediately across {$iterations} iterations");
    }

    /**
     * Test that answer events are dispatched immediately for broadcasting.
     */
    public function test_answer_events_are_dispatched_immediately_for_broadcasting(): void
    {
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([AnswerWasPosted::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'content' => 'Test answer ' . $i,
            ]);

            $startTime = microtime(true);
            
            // Dispatch event
            event(new AnswerWasPosted($answer));
            
            $endTime = microtime(true);
            $dispatchTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

            // Assert event was dispatched
            Event::assertDispatched(AnswerWasPosted::class);

            // Assert dispatch happened quickly
            $this->assertLessThan(100, $dispatchTime, 
                "Event dispatch took {$dispatchTime}ms, should be under 100ms");

            // Clean up
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Events dispatched immediately across {$iterations} iterations");
    }

    /**
     * Test that events implement ShouldBroadcast interface.
     * 
     * This ensures events are configured for real-time broadcasting.
     */
    public function test_events_implement_should_broadcast_interface(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        $questionEvent = new QuestionWasAsked($question);
        $this->assertInstanceOf(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            $questionEvent,
            'QuestionWasAsked should implement ShouldBroadcast'
        );

        $answer = Answer::factory()->create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);
        
        $answerEvent = new AnswerWasPosted($answer);
        $this->assertInstanceOf(
            \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
            $answerEvent,
            'AnswerWasPosted should implement ShouldBroadcast'
        );

        // Clean up
        $answer->delete();
        $question->delete();
        $user->delete();
    }

    /**
     * Test that broadcast data is serializable for transmission.
     * 
     * This ensures the data can be sent over WebSocket connections.
     */
    public function test_broadcast_data_is_serializable_for_any_event(): void
    {
        $iterations = 50;

        for ($i = 0; $i < $iterations; $i++) {
            $user = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            $event = new QuestionWasAsked($question);
            $broadcastData = $event->broadcastWith();

            // Attempt to serialize the data
            $serialized = json_encode($broadcastData);
            $this->assertNotFalse($serialized, 'Broadcast data should be JSON serializable');

            // Verify it can be deserialized
            $deserialized = json_decode($serialized, true);
            $this->assertIsArray($deserialized, 'Broadcast data should deserialize to array');
            $this->assertEquals($broadcastData['id'], $deserialized['id']);

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Broadcast data is serializable across {$iterations} iterations");
    }

    /**
     * Test that events have proper broadcast names for client-side listening.
     */
    public function test_events_have_proper_broadcast_names(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        
        $questionEvent = new QuestionWasAsked($question);
        $this->assertEquals('QuestionWasAsked', $questionEvent->broadcastAs());

        $answer = Answer::factory()->create([
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);
        
        $answerEvent = new AnswerWasPosted($answer);
        $this->assertEquals('AnswerWasPosted', $answerEvent->broadcastAs());

        // Clean up
        $answer->delete();
        $question->delete();
        $user->delete();
    }

    /**
     * Test that multiple events can be dispatched in rapid succession.
     * 
     * This simulates high-traffic scenarios where multiple users
     * are creating content simultaneously.
     */
    public function test_multiple_events_can_be_dispatched_rapidly(): void
    {
        Event::fake([QuestionWasAsked::class]);

        $eventCount = 50;
        $startTime = microtime(true);

        for ($i = 0; $i < $eventCount; $i++) {
            $user = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $user->id,
                'question' => 'Rapid test question ' . $i,
            ]);

            event(new QuestionWasAsked($question));
        }

        $endTime = microtime(true);
        $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert all events were dispatched
        Event::assertDispatchedTimes(QuestionWasAsked::class, $eventCount);

        // Assert total time is reasonable (under 2 seconds for 50 events)
        $this->assertLessThan(2000, $totalTime, 
            "Dispatching {$eventCount} events took {$totalTime}ms, should be under 2000ms");

        $this->assertTrue(true, "Dispatched {$eventCount} events in {$totalTime}ms");
    }
}
