<?php

namespace App\Listeners;

use App\Events\Answers\AnswerWasAccepted;
use Illuminate\Support\Facades\Log;

class LogAnswerAccepted
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AnswerWasAccepted $event): void
    {
        Log::info('Answer accepted', [
            'answer_id' => $event->answer->id,
            'question_id' => $event->answer->question_id,
            'user_id' => $event->answer->user_id,
        ]);
    }
}
