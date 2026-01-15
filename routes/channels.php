<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User-specific channel for personal notifications
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public questions channel - all authenticated users can listen
Broadcast::channel('questions', function ($user) {
    return $user !== null;
});

// Question-specific channel for answers and updates
Broadcast::channel('question.{questionId}', function ($user, $questionId) {
    // All authenticated users can listen to question updates
    return $user !== null;
});
