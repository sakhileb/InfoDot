<?php

namespace App\Listeners;

use App\Events\Questions\QuestionWasAsked;
use Illuminate\Support\Facades\Log;

class LogQuestionAsked
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
    public function handle(QuestionWasAsked $event): void
    {
        Log::info('New question asked', [
            'question_id' => $event->question->id,
            'user_id' => $event->question->user_id,
            'question' => $event->question->question,
        ]);
    }
}
