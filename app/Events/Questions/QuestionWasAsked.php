<?php

namespace App\Events\Questions;

use App\Models\Questions;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionWasAsked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Questions $question
    ) {}

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->question->id,
            'question' => $this->question->question,
            'description' => $this->question->description,
            'tags' => $this->question->tags,
            'user' => [
                'id' => $this->question->user->id,
                'name' => $this->question->user->name,
            ],
            'created_at' => $this->question->created_at?->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'QuestionWasAsked';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel|array
    {
        return new PrivateChannel('questions');
    }
}
