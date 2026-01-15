<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Models\Questions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuestionAnsweredNotification extends Notification implements ShouldQueue
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
            ->subject('New Answer to Your Question - InfoDot')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your question "' . $this->question->question . '" has received a new answer.')
            ->line('Answer by: ' . $this->answer->user->name)
            ->line('Answer preview: ' . \Illuminate\Support\Str::limit($this->answer->content, 100))
            ->action('View Answer', $url)
            ->line('Thank you for using InfoDot!');
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
            'answerer_name' => $this->answer->user->name,
            'answerer_id' => $this->answer->user_id,
            'content_preview' => \Illuminate\Support\Str::limit($this->answer->content, 100),
        ];
    }
}
