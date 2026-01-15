<?php

namespace App\Events\Answers;

use App\Models\Answer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnswerWasPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Answer $answer
    ) {}

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->answer->id,
            'content' => $this->answer->content,
            'question_id' => $this->answer->question_id,
            'is_accepted' => $this->answer->is_accepted,
            'user' => [
                'id' => $this->answer->user->id,
                'name' => $this->answer->user->name,
            ],
            'created_at' => $this->answer->created_at?->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'AnswerWasPosted';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('question.' . $this->answer->question_id);
    }
}
