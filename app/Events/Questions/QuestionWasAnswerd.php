<?php

namespace App\Events\Questions;


use App\Answer;
use Illuminate\Broadcasting\Channel;
use App\Http\Resources\AnswerResource;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class QuestionWasAnswerd implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $answer;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }

    public function broadcastWith()
    {
        return (new AnswerResource($this->answer))->jsonSerialize();
    }

    public function broadcastAs()
    {
        return 'QuestionWasAnswerd';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return $this->answer->user->followers->map(function ($user)
        {
            return new PrivateChannel('answer.' . $user->id);
        })->toArray();
    }

}
