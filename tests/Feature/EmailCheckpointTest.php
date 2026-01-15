<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\Team;
use App\Models\User;
use App\Notifications\AnswerAcceptedNotification;
use App\Notifications\QuestionAnsweredNotification;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Checkpoint Test: Email Functionality Verification
 * 
 * This test verifies all email functionality is working correctly:
 * - Contact form emails
 * - Question answered notifications
 * - Answer accepted notifications
 * - Team invitation notifications
 * - Email queueing
 * - Email templates
 */
class EmailCheckpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that contact form emails can be sent and queued
     */
    public function test_contact_form_email_can_be_sent(): void
    {
        Mail::fake();

        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message from the contact form.',
        ];

        Mail::to('support@infodot.com')->queue(new ContactMail($contactData));

        Mail::assertQueued(ContactMail::class, function ($mail) use ($contactData) {
            return $mail->hasTo('support@infodot.com') &&
                   $mail->details['name'] === $contactData['name'] &&
                   $mail->details['email'] === $contactData['email'] &&
                   $mail->details['subject'] === $contactData['subject'] &&
                   $mail->details['message'] === $contactData['message'];
        });
    }

    /**
     * Test that contact emails are queued for background processing
     */
    public function test_contact_emails_are_queued(): void
    {
        Queue::fake();

        $contactData = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'subject' => 'Inquiry',
            'message' => 'I have a question about your platform.',
        ];

        Mail::to('support@infodot.com')->queue(new ContactMail($contactData));

        Queue::assertPushed(\Illuminate\Mail\SendQueuedMailable::class);
    }

    /**
     * Test that question answered notifications can be sent
     */
    public function test_question_answered_notification_can_be_sent(): void
    {
        Notification::fake();

        $questionAuthor = User::factory()->create();
        $answerAuthor = User::factory()->create();
        
        $question = Questions::factory()->create([
            'user_id' => $questionAuthor->id,
            'question' => 'How do I test Laravel applications?',
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id,
            'content' => 'You can use PHPUnit and Laravel Dusk for testing.',
        ]);

        $questionAuthor->notify(new QuestionAnsweredNotification($answer, $question));

        Notification::assertSentTo(
            $questionAuthor,
            QuestionAnsweredNotification::class,
            function ($notification) use ($answer, $question) {
                return $notification->answer->id === $answer->id &&
                       $notification->question->id === $question->id;
            }
        );
    }

    /**
     * Test that answer accepted notifications can be sent
     */
    public function test_answer_accepted_notification_can_be_sent(): void
    {
        Notification::fake();

        $questionAuthor = User::factory()->create();
        $answerAuthor = User::factory()->create();
        
        $question = Questions::factory()->create([
            'user_id' => $questionAuthor->id,
            'question' => 'What is Laravel?',
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id,
            'content' => 'Laravel is a PHP web framework.',
            'is_accepted' => true,
        ]);

        $answerAuthor->notify(new AnswerAcceptedNotification($answer, $question));

        Notification::assertSentTo(
            $answerAuthor,
            AnswerAcceptedNotification::class,
            function ($notification) use ($answer, $question) {
                return $notification->answer->id === $answer->id &&
                       $notification->question->id === $question->id;
            }
        );
    }

    /**
     * Test that team invitation notifications can be sent
     */
    public function test_team_invitation_notification_can_be_sent(): void
    {
        Notification::fake();

        $teamOwner = User::factory()->create();
        $invitedUser = User::factory()->create();
        
        $team = Team::factory()->create([
            'user_id' => $teamOwner->id,
            'name' => 'Development Team',
        ]);

        $invitation = new \App\Models\TeamInvitation();
        $invitation->team_id = $team->id;
        $invitation->email = $invitedUser->email;
        $invitation->role = 'member';
        $invitation->save();

        $invitedUser->notify(new TeamInvitationNotification($invitation, $team));

        Notification::assertSentTo(
            $invitedUser,
            TeamInvitationNotification::class,
            function ($notification) use ($team, $invitation) {
                return $notification->team->id === $team->id &&
                       $notification->invitation->id === $invitation->id;
            }
        );
    }

    /**
     * Test that email templates render correctly
     */
    public function test_contact_email_template_renders_correctly(): void
    {
        $contactData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message.',
        ];

        $contactMail = new ContactMail($contactData);

        $rendered = $contactMail->render();

        $this->assertStringContainsString('Test User', $rendered);
        $this->assertStringContainsString('test@example.com', $rendered);
        $this->assertStringContainsString('This is a test message.', $rendered);
    }

    /**
     * Test that notification email templates render correctly
     */
    public function test_notification_email_templates_render_correctly(): void
    {
        $questionAuthor = User::factory()->create(['name' => 'Question Author']);
        $answerAuthor = User::factory()->create(['name' => 'Answer Author']);
        
        $question = Questions::factory()->create([
            'user_id' => $questionAuthor->id,
            'question' => 'Test Question?',
        ]);

        $answer = Answer::factory()->create([
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id,
            'content' => 'Test Answer Content',
        ]);

        // Test QuestionAnsweredNotification
        $notification = new QuestionAnsweredNotification($answer, $question);
        $mailMessage = $notification->toMail($questionAuthor);
        
        $this->assertStringContainsString('New Answer', $mailMessage->subject);
        $this->assertNotEmpty($mailMessage->introLines);
        $this->assertNotEmpty($mailMessage->actionText);
        $this->assertNotEmpty($mailMessage->actionUrl);

        // Test AnswerAcceptedNotification
        $acceptedNotification = new AnswerAcceptedNotification($answer, $question);
        $acceptedMailMessage = $acceptedNotification->toMail($answerAuthor);
        
        $this->assertStringContainsString('Answer Was Accepted', $acceptedMailMessage->subject);
        $this->assertNotEmpty($acceptedMailMessage->introLines);
    }

    /**
     * Test that notifications are queued for background processing
     */
    public function test_notifications_are_queued(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $user->notify(new QuestionAnsweredNotification($answer, $question));

        Queue::assertPushed(\Illuminate\Notifications\SendQueuedNotifications::class);
    }

    /**
     * Test that notification channels are configured correctly
     */
    public function test_notification_channels_are_configured(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $notification = new QuestionAnsweredNotification($answer, $question);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /**
     * Test that notification database payload is correct
     */
    public function test_notification_database_payload_is_correct(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['question' => 'Test Question']);
        $answer = Answer::factory()->create([
            'question_id' => $question->id,
            'content' => 'Test Answer',
        ]);

        $notification = new QuestionAnsweredNotification($answer, $question);
        $array = $notification->toArray($user);

        $this->assertArrayHasKey('answer_id', $array);
        $this->assertArrayHasKey('question_id', $array);
        $this->assertArrayHasKey('content_preview', $array);
        $this->assertArrayHasKey('question_title', $array);
        $this->assertEquals($answer->id, $array['answer_id']);
        $this->assertEquals($question->id, $array['question_id']);
    }

    /**
     * Test that emails work with different mail drivers
     */
    public function test_emails_work_with_different_drivers(): void
    {
        $drivers = ['smtp', 'log', 'array'];

        foreach ($drivers as $driver) {
            config(['mail.default' => $driver]);

            Mail::fake();

            $contactData = [
                'name' => 'Test User',
                'email' => 'user@example.com',
                'subject' => 'Test Subject',
                'message' => 'Test Message',
            ];

            Mail::to('test@example.com')->queue(new ContactMail($contactData));

            Mail::assertQueued(ContactMail::class);
        }
    }

    /**
     * Test that queue processing works for emails
     */
    public function test_queue_processing_works_for_emails(): void
    {
        Queue::fake();

        // Queue multiple emails
        for ($i = 0; $i < 5; $i++) {
            $contactData = [
                'name' => "User $i",
                'email' => "user$i@example.com",
                'subject' => "Subject $i",
                'message' => "Message $i",
            ];

            Mail::to('test@example.com')->queue(new ContactMail($contactData));
        }

        // Verify all emails were queued
        Queue::assertPushed(\Illuminate\Mail\SendQueuedMailable::class, 5);
    }

    /**
     * Test that email from address is configured correctly
     */
    public function test_email_from_address_is_configured(): void
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        $this->assertNotEmpty($fromAddress);
        $this->assertNotEmpty($fromName);
        $this->assertStringContainsString('@', $fromAddress);
    }

    /**
     * Test that mail configuration is valid
     */
    public function test_mail_configuration_is_valid(): void
    {
        $this->assertNotEmpty(config('mail.default'));
        $this->assertIsArray(config('mail.mailers'));
        $this->assertArrayHasKey(config('mail.default'), config('mail.mailers'));
    }

    /**
     * Test comprehensive email workflow
     */
    public function test_comprehensive_email_workflow(): void
    {
        Mail::fake();
        Notification::fake();

        // 1. User sends contact form
        $contactData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'subject' => 'Help Request',
            'message' => 'I need help with my account.',
        ];

        Mail::to('support@infodot.com')->queue(new ContactMail($contactData));

        // 2. User asks a question
        $questionAuthor = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);

        // 3. Another user answers the question
        $answerAuthor = User::factory()->create();
        $answer = Answer::factory()->create([
            'user_id' => $answerAuthor->id,
            'question_id' => $question->id,
        ]);

        // 4. Question author gets notified
        $questionAuthor->notify(new QuestionAnsweredNotification($answer, $question));

        // 5. Question author accepts the answer
        $answer->update(['is_accepted' => true]);

        // 6. Answer author gets notified
        $answerAuthor->notify(new AnswerAcceptedNotification($answer, $question));

        // 7. User gets invited to a team
        $team = Team::factory()->create();
        $invitedUser = User::factory()->create();
        $invitation = new \App\Models\TeamInvitation();
        $invitation->team_id = $team->id;
        $invitation->email = $invitedUser->email;
        $invitation->role = 'member';
        $invitation->save();
        $invitedUser->notify(new TeamInvitationNotification($invitation, $team));

        // Verify all emails/notifications were processed
        Mail::assertQueued(ContactMail::class);

        Notification::assertSentTo($questionAuthor, QuestionAnsweredNotification::class);
        Notification::assertSentTo($answerAuthor, AnswerAcceptedNotification::class);
        Notification::assertSentTo($invitedUser, TeamInvitationNotification::class);
    }
}
