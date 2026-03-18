<?php

namespace App\Events\Questions;


use App\Models\Questions;
use Illuminate\Broadcasting\Channel;
use App\Http\Resources\QuestionsResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class QuestionWasAsked implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $question;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Questions $question)
    {
        $this->question = $question;
    }

    public function broadcastWith()
    {
        return (new QuestionsResource($this->question))->jsonSerialize();
    }

    public function broadcastAs()
    {
        return 'QuestionWasAsked';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('question');
    }

}
