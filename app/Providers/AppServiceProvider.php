<?php

namespace App\Providers;

use App\Events\Answers\AnswerWasAccepted;
use App\Events\Answers\AnswerWasPosted;
use App\Events\Questions\QuestionWasAsked;
use App\Listeners\LogAnswerAccepted;
use App\Listeners\LogAnswerPosted;
use App\Listeners\LogQuestionAsked;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            QuestionWasAsked::class,
            LogQuestionAsked::class,
        );

        Event::listen(
            AnswerWasPosted::class,
            LogAnswerPosted::class,
        );

        Event::listen(
            AnswerWasAccepted::class,
            LogAnswerAccepted::class,
        );
    }
}
