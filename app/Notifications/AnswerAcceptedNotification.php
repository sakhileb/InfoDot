<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Models\Questions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AnswerAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Answer $answer,
        public Questions $question
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('questions.view', ['qid' => $this->question->id]);

        return (new MailMessage)
            ->subject('Your Answer Was Accepted! - InfoDot')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your answer to the question "' . $this->question->question . '" has been accepted!')
            ->line('Question by: ' . $this->question->user->name)
            ->line('Your answer: ' . \Illuminate\Support\Str::limit($this->answer->content, 100))
            ->action('View Question', $url)
            ->line('Thank you for contributing to the InfoDot community!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'answer_id' => $this->answer->id,
            'question_id' => $this->question->id,
            'question_title' => $this->question->question,
            'question_author_name' => $this->question->user->name,
            'question_author_id' => $this->question->user_id,
            'content_preview' => \Illuminate\Support\Str::limit($this->answer->content, 100),
        ];
    }
}
