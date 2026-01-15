<?php

namespace App\Listeners;

use App\Events\Answers\AnswerWasPosted;
use Illuminate\Support\Facades\Log;

class LogAnswerPosted
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
    public function handle(AnswerWasPosted $event): void
    {
        Log::info('New answer posted', [
            'answer_id' => $event->answer->id,
            'question_id' => $event->answer->question_id,
            'user_id' => $event->answer->user_id,
        ]);
    }
}
