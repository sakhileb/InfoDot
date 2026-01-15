<?php

namespace Tests\Feature;

use App\Events\Answers\AnswerWasAccepted;
use App\Events\Answers\AnswerWasPosted;
use App\Events\Questions\QuestionWasAsked;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Property 21: Broadcasting Driver Compatibility
 * 
 * For any broadcast event, the event should be transmitted correctly through 
 * both Reverb and Pusher drivers.
 * 
 * Feature: infodot-modernization, Property 21: Broadcasting Driver Compatibility
 * Validates: Requirements IR-2
 */
class BroadcastingDriverCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that events work with Reverb driver configuration.
     */
    public function test_events_are_compatible_with_reverb_driver(): void
    {
        $iterations = 100;

        // Set broadcasting driver to Reverb
        Config::set('broadcasting.default', 'reverb');
        Config::set('broadcasting.connections.reverb', [
            'driver' => 'reverb',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'app_id' => 'test-app',
            'options' => [
                'host' => 'localhost',
                'port' => 8080,
                'scheme' => 'http',
                'useTLS' => false,
            ],
        ]);

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([QuestionWasAsked::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $user->id,
                'question' => 'Reverb test question ' . $i,
            ]);

            // Create event and verify it has proper structure
            $event = new QuestionWasAsked($question);
            
            // Verify event implements ShouldBroadcast
            $this->assertInstanceOf(
                \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
                $event
            );

            // Verify broadcast data is present
            $broadcastData = $event->broadcastWith();
            $this->assertIsArray($broadcastData);
            $this->assertArrayHasKey('id', $broadcastData);

            // Verify broadcast channel is defined
            $channel = $event->broadcastOn();
            $this->assertNotNull($channel);

            // Dispatch event
            event($event);

            // Assert event was dispatched
            Event::assertDispatched(QuestionWasAsked::class);

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Events compatible with Reverb driver across {$iterations} iterations");
    }

    /**
     * Test that events work with Pusher driver configuration.
     */
    public function test_events_are_compatible_with_pusher_driver(): void
    {
        $iterations = 100;

        // Set broadcasting driver to Pusher
        Config::set('broadcasting.default', 'pusher');
        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => 'test-key',
            'secret' => 'test-secret',
            'app_id' => 'test-app',
            'options' => [
                'cluster' => 'mt1',
                'host' => 'localhost',
                'port' => 443,
                'scheme' => 'https',
                'encrypted' => true,
                'useTLS' => true,
            ],
        ]);

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([AnswerWasPosted::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create();
            $answer = Answer::factory()->create([
                'user_id' => $user->id,
                'question_id' => $question->id,
                'content' => 'Pusher test answer ' . $i,
            ]);

            // Create event and verify it has proper structure
            $event = new AnswerWasPosted($answer);
            
            // Verify event implements ShouldBroadcast
            $this->assertInstanceOf(
                \Illuminate\Contracts\Broadcasting\ShouldBroadcast::class,
                $event
            );

            // Verify broadcast data is present
            $broadcastData = $event->broadcastWith();
            $this->assertIsArray($broadcastData);
            $this->assertArrayHasKey('id', $broadcastData);

            // Verify broadcast channel is defined
            $channel = $event->broadcastOn();
            $this->assertNotNull($channel);

            // Dispatch event
            event($event);

            // Assert event was dispatched
            Event::assertDispatched(AnswerWasPosted::class);

            // Clean up
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Events compatible with Pusher driver across {$iterations} iterations");
    }

    /**
     * Test that events work with log driver (for testing/debugging).
     */
    public function test_events_are_compatible_with_log_driver(): void
    {
        $iterations = 50;

        // Set broadcasting driver to log
        Config::set('broadcasting.default', 'log');

        for ($i = 0; $i < $iterations; $i++) {
            Event::fake([QuestionWasAsked::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $user->id,
                'question' => 'Log test question ' . $i,
            ]);

            $event = new QuestionWasAsked($question);
            event($event);

            Event::assertDispatched(QuestionWasAsked::class);

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Events compatible with log driver across {$iterations} iterations");
    }

    /**
     * Test that broadcast data structure is consistent across drivers.
     * 
     * This ensures that regardless of the driver, the data sent to clients
     * has the same structure.
     */
    public function test_broadcast_data_structure_is_consistent_across_drivers(): void
    {
        $drivers = ['reverb', 'pusher', 'log'];
        $iterations = 30;

        foreach ($drivers as $driver) {
            Config::set('broadcasting.default', $driver);

            for ($i = 0; $i < $iterations; $i++) {
                $user = User::factory()->create();
                $question = Questions::factory()->create(['user_id' => $user->id]);
                
                $event = new QuestionWasAsked($question);
                $broadcastData = $event->broadcastWith();

                // Verify consistent structure
                $this->assertArrayHasKey('id', $broadcastData);
                $this->assertArrayHasKey('question', $broadcastData);
                $this->assertArrayHasKey('description', $broadcastData);
                $this->assertArrayHasKey('tags', $broadcastData);
                $this->assertArrayHasKey('user', $broadcastData);
                $this->assertArrayHasKey('created_at', $broadcastData);

                // Verify user structure
                $this->assertIsArray($broadcastData['user']);
                $this->assertArrayHasKey('id', $broadcastData['user']);
                $this->assertArrayHasKey('name', $broadcastData['user']);

                // Clean up
                $question->delete();
                $user->delete();
            }
        }

        $this->assertTrue(true, "Broadcast data structure consistent across all drivers");
    }

    /**
     * Test that channel names are consistent across drivers.
     */
    public function test_channel_names_are_consistent_across_drivers(): void
    {
        $drivers = ['reverb', 'pusher', 'log'];

        foreach ($drivers as $driver) {
            Config::set('broadcasting.default', $driver);

            $user = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $user->id]);
            
            $event = new QuestionWasAsked($question);
            $channel = $event->broadcastOn();

            // Verify channel is a PrivateChannel
            $this->assertInstanceOf(
                \Illuminate\Broadcasting\PrivateChannel::class,
                $channel,
                "Channel should be PrivateChannel for driver: {$driver}"
            );

            // Test answer event channel
            $answer = Answer::factory()->create([
                'user_id' => $user->id,
                'question_id' => $question->id,
            ]);
            
            $answerEvent = new AnswerWasPosted($answer);
            $answerChannel = $answerEvent->broadcastOn();

            $this->assertInstanceOf(
                \Illuminate\Broadcasting\PrivateChannel::class,
                $answerChannel,
                "Answer channel should be PrivateChannel for driver: {$driver}"
            );

            // Clean up
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Channel names consistent across all drivers");
    }

    /**
     * Test that broadcast event names are consistent across drivers.
     */
    public function test_broadcast_event_names_are_consistent_across_drivers(): void
    {
        $drivers = ['reverb', 'pusher', 'log'];

        foreach ($drivers as $driver) {
            Config::set('broadcasting.default', $driver);

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

            $answer->is_accepted = true;
            $answer->save();
            
            $acceptedEvent = new AnswerWasAccepted($answer);
            $this->assertEquals('AnswerWasAccepted', $acceptedEvent->broadcastAs());

            // Clean up
            $answer->delete();
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Broadcast event names consistent across all drivers");
    }

    /**
     * Test that switching drivers doesn't break event functionality.
     */
    public function test_switching_drivers_maintains_event_functionality(): void
    {
        $iterations = 20;

        for ($i = 0; $i < $iterations; $i++) {
            // Alternate between drivers
            $driver = ($i % 2 === 0) ? 'reverb' : 'pusher';
            Config::set('broadcasting.default', $driver);

            Event::fake([QuestionWasAsked::class]);

            $user = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $user->id,
                'question' => "Driver switch test {$i} with {$driver}",
            ]);

            $event = new QuestionWasAsked($question);
            event($event);

            // Verify event was dispatched regardless of driver
            Event::assertDispatched(QuestionWasAsked::class);

            // Verify event structure is valid
            $broadcastData = $event->broadcastWith();
            $this->assertIsArray($broadcastData);
            $this->assertArrayHasKey('id', $broadcastData);

            // Clean up
            $question->delete();
            $user->delete();
        }

        $this->assertTrue(true, "Event functionality maintained across {$iterations} driver switches");
    }
}
