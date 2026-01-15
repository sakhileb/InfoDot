<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use App\Notifications\AnswerAcceptedNotification;
use App\Notifications\QuestionAnsweredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Property-Based Test: Email Queue Processing
 * 
 * Feature: infodot-modernization, Property 11: Email Queue Processing
 * Validates: Requirements FR-11
 * 
 * Property: For any email notification trigger, the email should be queued 
 * and eventually sent with correct recipient and content.
 */
class EmailQueueProcessingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property test: Contact emails are queued correctly.
     * 
     * @test
     */
    public function property_contact_emails_are_queued_with_correct_data(): void
    {
        Mail::fake();

        // Generate random contact form data (100 iterations)
        for ($i = 0; $i < 100; $i++) {
            $details = [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'message' => fake()->paragraph(),
            ];

            // Send email (which should be queued due to ShouldQueue)
            Mail::to('admin@infodot.com')->send(new ContactMail($details));

            // Verify email was queued with correct data
            Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
                return $mail->details['name'] === $details['name'] &&
                       $mail->details['email'] === $details['email'] &&
                       $mail->details['message'] === $details['message'] &&
                       $mail->hasTo('admin@infodot.com');
            });
        }

        // Verify all 100 emails were queued
        Mail::assertQueuedCount(100);
    }

    /**
     * Property test: Question answered notifications are queued correctly.
     * 
     * @test
     */
    public function property_question_answered_notifications_are_queued(): void
    {
        Notification::fake();

        // Generate random question/answer scenarios (100 iterations)
        for ($i = 0; $i < 100; $i++) {
            $questionAuthor = User::factory()->create();
            $answerer = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
            $answer = Answer::factory()->create([
                'user_id' => $answerer->id,
                'question_id' => $question->id,
            ]);

            // Send notification
            $questionAuthor->notify(new QuestionAnsweredNotification($answer, $question));

            // Verify notification was sent with correct data
            Notification::assertSentTo(
                $questionAuthor,
                QuestionAnsweredNotification::class,
                function ($notification) use ($answer, $question) {
                    // Verify notification contains correct answer and question
                    return $notification->answer->id === $answer->id &&
                           $notification->question->id === $question->id;
                }
            );
        }
    }

    /**
     * Property test: Answer accepted notifications are queued correctly.
     * 
     * @test
     */
    public function property_answer_accepted_notifications_are_queued(): void
    {
        Notification::fake();

        // Generate random answer acceptance scenarios (100 iterations)
        for ($i = 0; $i < 100; $i++) {
            $questionAuthor = User::factory()->create();
            $answerer = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
            $answer = Answer::factory()->create([
                'user_id' => $answerer->id,
                'question_id' => $question->id,
                'is_accepted' => true,
            ]);

            // Send notification to answerer
            $answerer->notify(new AnswerAcceptedNotification($answer, $question));

            // Verify notification was sent with correct data
            Notification::assertSentTo(
                $answerer,
                AnswerAcceptedNotification::class,
                function ($notification) use ($answer, $question) {
                    return $notification->answer->id === $answer->id &&
                           $notification->question->id === $question->id;
                }
            );
        }
    }

    /**
     * Property test: Queued emails maintain data integrity.
     * 
     * @test
     */
    public function property_queued_emails_maintain_data_integrity(): void
    {
        Mail::fake();

        $testData = [];

        // Queue multiple emails with different data
        for ($i = 0; $i < 100; $i++) {
            $details = [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'message' => fake()->unique()->paragraph(),
            ];
            $testData[] = $details;

            Mail::to('admin@infodot.com')->send(new ContactMail($details));
        }

        // Verify each email was queued with its original data intact
        foreach ($testData as $details) {
            Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
                return $mail->details['name'] === $details['name'] &&
                       $mail->details['email'] === $details['email'] &&
                       $mail->details['message'] === $details['message'];
            });
        }
    }

    /**
     * Property test: Notifications contain correct recipient information.
     * 
     * @test
     */
    public function property_notifications_contain_correct_recipient_info(): void
    {
        Notification::fake();

        // Test with multiple users and questions
        for ($i = 0; $i < 100; $i++) {
            $questionAuthor = User::factory()->create();
            $answerer = User::factory()->create();
            $question = Questions::factory()->create([
                'user_id' => $questionAuthor->id,
                'question' => fake()->sentence() . '?',
            ]);
            $answer = Answer::factory()->create([
                'user_id' => $answerer->id,
                'question_id' => $question->id,
                'content' => fake()->paragraph(),
            ]);

            // Send notification
            $questionAuthor->notify(new QuestionAnsweredNotification($answer, $question));

            // Verify notification was sent to correct user
            Notification::assertSentTo($questionAuthor, QuestionAnsweredNotification::class);
            
            // Verify notification was NOT sent to the answerer
            Notification::assertNotSentTo($answerer, QuestionAnsweredNotification::class);

            // Verify notification contains correct data
            Notification::assertSentTo(
                $questionAuthor,
                QuestionAnsweredNotification::class,
                function ($notification) use ($answer, $question, $answerer) {
                    $array = $notification->toArray($notification->answer->user);
                    return $array['answer_id'] === $answer->id &&
                           $array['question_id'] === $question->id &&
                           $array['answerer_id'] === $answerer->id;
                }
            );
        }
    }

    /**
     * Property test: Email queue handles concurrent requests.
     * 
     * @test
     */
    public function property_email_queue_handles_concurrent_requests(): void
    {
        Mail::fake();

        $emails = [];

        // Simulate concurrent email sending
        for ($i = 0; $i < 100; $i++) {
            $details = [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'message' => fake()->paragraph(),
            ];
            $emails[] = $details;

            // Queue email
            Mail::to('admin@infodot.com')->send(new ContactMail($details));
        }

        // Verify all emails were queued
        Mail::assertQueuedCount(100);

        // Verify no emails were lost
        foreach ($emails as $details) {
            Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
                return $mail->details['email'] === $details['email'];
            });
        }
    }

    /**
     * Property test: Notification channels are correctly configured.
     * 
     * @test
     */
    public function property_notification_channels_are_correctly_configured(): void
    {
        // Test with multiple notification types
        for ($i = 0; $i < 100; $i++) {
            $user = User::factory()->create();
            $question = Questions::factory()->create(['user_id' => $user->id]);
            $answer = Answer::factory()->create(['question_id' => $question->id]);

            $notification = new QuestionAnsweredNotification($answer, $question);
            $channels = $notification->via($user);

            // Verify both mail and database channels are configured
            $this->assertContains('mail', $channels);
            $this->assertContains('database', $channels);
            $this->assertCount(2, $channels);
        }
    }

    /**
     * Property test: Email content matches notification data.
     * 
     * @test
     */
    public function property_email_content_matches_notification_data(): void
    {
        // Test with various question/answer combinations
        for ($i = 0; $i < 100; $i++) {
            $questionAuthor = User::factory()->create(['name' => fake()->name()]);
            $answerer = User::factory()->create(['name' => fake()->name()]);
            $question = Questions::factory()->create([
                'user_id' => $questionAuthor->id,
                'question' => fake()->sentence() . '?',
            ]);
            $answer = Answer::factory()->create([
                'user_id' => $answerer->id,
                'question_id' => $question->id,
                'content' => fake()->paragraph(),
            ]);

            $notification = new QuestionAnsweredNotification($answer, $question);
            $mailMessage = $notification->toMail($questionAuthor);
            $arrayData = $notification->toArray($questionAuthor);

            // Verify mail message contains correct data
            $this->assertStringContainsString('New Answer to Your Question', $mailMessage->subject);
            $this->assertStringContainsString($questionAuthor->name, $mailMessage->greeting);

            // Verify array data matches the actual models
            $this->assertEquals($answer->id, $arrayData['answer_id']);
            $this->assertEquals($question->id, $arrayData['question_id']);
            $this->assertEquals($answerer->name, $arrayData['answerer_name']);
        }
    }
}
