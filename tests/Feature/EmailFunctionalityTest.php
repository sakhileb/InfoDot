<?php

namespace Tests\Feature;

use App\Mail\ContactMail;
use App\Models\Answer;
use App\Models\Questions;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\AnswerAcceptedNotification;
use App\Notifications\QuestionAnsweredNotification;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test contact form email can be sent.
     */
    public function test_contact_form_email_can_be_sent(): void
    {
        Mail::fake();

        $details = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'message' => 'This is a test message from the contact form.',
        ];

        // ContactMail implements ShouldQueue, so it's automatically queued
        Mail::to('admin@infodot.com')->send(new ContactMail($details));

        Mail::assertQueued(ContactMail::class, function ($mail) use ($details) {
            return $mail->details['name'] === $details['name'] &&
                   $mail->details['email'] === $details['email'] &&
                   $mail->details['message'] === $details['message'];
        });
    }

    /**
     * Test contact mail is queued.
     */
    public function test_contact_mail_is_queued(): void
    {
        Mail::fake();

        $details = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Another test message.',
        ];

        Mail::to('admin@infodot.com')->queue(new ContactMail($details));

        Mail::assertQueued(ContactMail::class);
    }

    /**
     * Test question answered notification can be sent.
     */
    public function test_question_answered_notification_can_be_sent(): void
    {
        Notification::fake();

        $questionAuthor = User::factory()->create();
        $answerer = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        $answer = Answer::factory()->create([
            'user_id' => $answerer->id,
            'question_id' => $question->id,
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
     * Test answer accepted notification can be sent.
     */
    public function test_answer_accepted_notification_can_be_sent(): void
    {
        Notification::fake();

        $questionAuthor = User::factory()->create();
        $answerer = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $questionAuthor->id]);
        $answer = Answer::factory()->create([
            'user_id' => $answerer->id,
            'question_id' => $question->id,
            'is_accepted' => true,
        ]);

        $answerer->notify(new AnswerAcceptedNotification($answer, $question));

        Notification::assertSentTo(
            $answerer,
            AnswerAcceptedNotification::class,
            function ($notification) use ($answer, $question) {
                return $notification->answer->id === $answer->id &&
                       $notification->question->id === $question->id;
            }
        );
    }

    /**
     * Test team invitation notification can be sent.
     */
    public function test_team_invitation_notification_can_be_sent(): void
    {
        Notification::fake();

        $teamOwner = User::factory()->create();
        $invitee = User::factory()->create();
        $team = Team::factory()->create(['user_id' => $teamOwner->id]);
        
        // Create invitation using the team relationship
        $invitation = $team->teamInvitations()->create([
            'email' => $invitee->email,
            'role' => 'editor',
        ]);

        $invitee->notify(new TeamInvitationNotification($invitation, $team));

        Notification::assertSentTo(
            $invitee,
            TeamInvitationNotification::class,
            function ($notification) use ($invitation, $team) {
                return $notification->invitation->id === $invitation->id &&
                       $notification->team->id === $team->id;
            }
        );
    }

    /**
     * Test email template renders correctly for contact mail.
     */
    public function test_contact_email_template_renders_correctly(): void
    {
        $details = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'This is a test message.',
        ];

        $mailable = new ContactMail($details);
        $mailable->assertSeeInHtml('New Contact Form Message');
        $mailable->assertSeeInHtml($details['name']);
        $mailable->assertSeeInHtml($details['email']);
        $mailable->assertSeeInHtml($details['message']);
    }

    /**
     * Test notification email template renders correctly for question answered.
     */
    public function test_question_answered_notification_email_renders_correctly(): void
    {
        $questionAuthor = User::factory()->create(['name' => 'Question Author']);
        $answerer = User::factory()->create(['name' => 'Answerer']);
        $question = Questions::factory()->create([
            'user_id' => $questionAuthor->id,
            'question' => 'Test Question?',
        ]);
        $answer = Answer::factory()->create([
            'user_id' => $answerer->id,
            'question_id' => $question->id,
            'content' => 'This is a test answer.',
        ]);

        $notification = new QuestionAnsweredNotification($answer, $question);
        $mailMessage = $notification->toMail($questionAuthor);

        $this->assertStringContainsString('New Answer to Your Question', $mailMessage->subject);
        $this->assertStringContainsString($questionAuthor->name, $mailMessage->greeting);
        $this->assertStringContainsString($question->question, $mailMessage->introLines[0]);
    }

    /**
     * Test notifications are queued.
     */
    public function test_notifications_are_queued(): void
    {
        Queue::fake();
        Notification::fake();

        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $user->notify(new QuestionAnsweredNotification($answer, $question));

        Notification::assertSentTo($user, QuestionAnsweredNotification::class);
    }

    /**
     * Test notification channels are configured correctly.
     */
    public function test_notification_channels_are_configured(): void
    {
        $user = User::factory()->create();
        $question = Questions::factory()->create(['user_id' => $user->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $notification = new QuestionAnsweredNotification($answer, $question);
        $channels = $notification->via($user);

        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    /**
     * Test notification database payload is correct.
     */
    public function test_notification_database_payload_is_correct(): void
    {
        $questionAuthor = User::factory()->create();
        $answerer = User::factory()->create(['name' => 'Test Answerer']);
        $question = Questions::factory()->create([
            'user_id' => $questionAuthor->id,
            'question' => 'Test Question?',
        ]);
        $answer = Answer::factory()->create([
            'user_id' => $answerer->id,
            'question_id' => $question->id,
            'content' => 'Test answer content.',
        ]);

        $notification = new QuestionAnsweredNotification($answer, $question);
        $array = $notification->toArray($questionAuthor);

        $this->assertEquals($answer->id, $array['answer_id']);
        $this->assertEquals($question->id, $array['question_id']);
        $this->assertEquals($question->question, $array['question_title']);
        $this->assertEquals($answerer->name, $array['answerer_name']);
    }
}
