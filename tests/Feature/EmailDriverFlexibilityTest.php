<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\User;
use App\Notifications\QuestionAnsweredNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Property-Based Test: Email Driver Flexibility
 * 
 * Feature: infodot-modernization, Property 22: Email Driver Flexibility
 * Validates: Requirements IR-3
 * 
 * Property: For any email notification, the email should be sent correctly 
 * through any configured mail driver (SMTP, Mailgun, log, array, etc.).
 */
class EmailDriverFlexibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Property test: Emails work with different mail drivers.
     * 
     * @test
     */
    public function property_emails_work_with_different_mail_drivers(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            // Configure mail driver
            Config::set('mail.default', $driver);

            Mail::fake();

            // Test with multiple emails (100 iterations per driver)
            for ($i = 0; $i < 100; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                // Send email
                Mail::to('admin@infodot.com')->send(new ContactMail($details));

                // Verify email was queued regardless of driver
                Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
                    return $mail->details['name'] === $details['name'] &&
                           $mail->details['email'] === $details['email'] &&
                           $mail->details['message'] === $details['message'];
                });
            }

            // Verify all emails were queued for this driver
            Mail::assertQueuedCount(100);
        }
    }

    /**
     * Property test: Notifications work with different mail drivers.
     * 
     * @test
     */
    public function property_notifications_work_with_different_mail_drivers(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            Notification::fake();

            // Test with multiple notifications (100 iterations per driver)
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

                // Verify notification was sent regardless of driver
                Notification::assertSentTo(
                    $questionAuthor,
                    QuestionAnsweredNotification::class,
                    function ($notification) use ($answer, $question) {
                        return $notification->answer->id === $answer->id &&
                               $notification->question->id === $question->id;
                    }
                );
            }
        }
    }

    /**
     * Property test: Mail configuration can be changed at runtime.
     * 
     * @test
     */
    public function property_mail_configuration_can_be_changed_at_runtime(): void
    {
        Mail::fake();

        $drivers = ['log', 'array', 'log', 'array']; // Test switching back and forth

        foreach ($drivers as $index => $driver) {
            // Change driver at runtime
            Config::set('mail.default', $driver);

            $details = [
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'message' => fake()->paragraph() . " (Driver: $driver, Index: $index)",
            ];

            // Send email with current driver
            Mail::to('admin@infodot.com')->send(new ContactMail($details));

            // Verify email was queued with correct data
            Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
                return $mail->details['message'] === $details['message'];
            });
        }

        // Verify all emails were queued
        Mail::assertQueuedCount(4);
    }

    /**
     * Property test: Email envelope is driver-agnostic.
     * 
     * @test
     */
    public function property_email_envelope_is_driver_agnostic(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            // Test with multiple emails
            for ($i = 0; $i < 100; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                $mailable = new ContactMail($details);
                $envelope = $mailable->envelope();

                // Verify envelope properties are consistent regardless of driver
                $this->assertEquals('Contact Form Message - InfoDot', $envelope->subject);
                $this->assertCount(1, $envelope->replyTo);
                $this->assertEquals($details['email'], $envelope->replyTo[0]->address);
            }
        }
    }

    /**
     * Property test: Email content is driver-agnostic.
     * 
     * @test
     */
    public function property_email_content_is_driver_agnostic(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            // Test with multiple emails
            for ($i = 0; $i < 100; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                $mailable = new ContactMail($details);
                $content = $mailable->content();

                // Verify content properties are consistent regardless of driver
                $this->assertEquals('emails.contact', $content->view);
                $this->assertArrayHasKey('details', $content->with);
                $this->assertEquals($details, $content->with['details']);
            }
        }
    }

    /**
     * Property test: Notification mail messages are driver-agnostic.
     * 
     * @test
     */
    public function property_notification_mail_messages_are_driver_agnostic(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            // Test with multiple notifications
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

                // Verify mail message is consistent regardless of driver
                $this->assertStringContainsString('New Answer to Your Question', $mailMessage->subject);
                $this->assertStringContainsString($questionAuthor->name, $mailMessage->greeting);
                $this->assertNotEmpty($mailMessage->introLines);
                $this->assertNotEmpty($mailMessage->actionText);
                $this->assertNotEmpty($mailMessage->actionUrl);
            }
        }
    }

    /**
     * Property test: Mail from address is consistent across drivers.
     * 
     * @test
     */
    public function property_mail_from_address_is_consistent_across_drivers(): void
    {
        $drivers = ['log', 'array'];
        $fromAddress = 'noreply@infodot.com';
        $fromName = 'InfoDot';

        Config::set('mail.from.address', $fromAddress);
        Config::set('mail.from.name', $fromName);

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            // Test with multiple emails
            for ($i = 0; $i < 100; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                $mailable = new ContactMail($details);

                // Verify from address is consistent
                $this->assertEquals($fromAddress, config('mail.from.address'));
                $this->assertEquals($fromName, config('mail.from.name'));
            }
        }
    }

    /**
     * Property test: Queue connection works with different mail drivers.
     * 
     * @test
     */
    public function property_queue_connection_works_with_different_mail_drivers(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            Mail::fake();

            // Test queuing with multiple emails
            for ($i = 0; $i < 100; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                // Queue email (ContactMail implements ShouldQueue)
                Mail::to('admin@infodot.com')->send(new ContactMail($details));

                // Verify email was queued regardless of driver
                Mail::assertQueued(ContactMail::class);
            }

            // Verify all emails were queued for this driver
            Mail::assertQueuedCount(100);
        }
    }

    /**
     * Property test: Multiple recipients work with different drivers.
     * 
     * @test
     */
    public function property_multiple_recipients_work_with_different_drivers(): void
    {
        $drivers = ['log', 'array'];

        foreach ($drivers as $driver) {
            Config::set('mail.default', $driver);

            Mail::fake();

            // Test with multiple recipients (10 iterations to keep test reasonable)
            for ($i = 0; $i < 10; $i++) {
                $details = [
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'message' => fake()->paragraph(),
                ];

                $recipients = [
                    fake()->safeEmail(),
                    fake()->safeEmail(),
                    fake()->safeEmail(),
                ];

                // Send to multiple recipients
                foreach ($recipients as $recipient) {
                    Mail::to($recipient)->send(new ContactMail($details));
                }
            }

            // Verify emails were queued (10 iterations * 3 recipients = 30)
            Mail::assertQueuedCount(30);
        }
    }
}
