<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AnswerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// -------------------------------------------------------
// User API Routes
// -------------------------------------------------------
Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'show']);

// -------------------------------------------------------
// Answer API Routes
// -------------------------------------------------------
// Public routes (no authentication required)
Route::get('answers', [AnswerController::class, 'index'])
    ->name('api.answers.index')
    ->middleware('throttle:60,1');
Route::get('answers/{answer}', [AnswerController::class, 'show'])
    ->name('api.answers.show')
    ->middleware('throttle:60,1');
Route::get('questions/{question}/answers', [AnswerController::class, 'byQuestion'])
    ->name('api.questions.answers')
    ->middleware('throttle:60,1');

// Protected routes (require authentication)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('answers', [AnswerController::class, 'store'])
        ->name('api.answers.store');
    Route::put('answers/{answer}', [AnswerController::class, 'update'])
        ->name('api.answers.update');
    Route::delete('answers/{answer}', [AnswerController::class, 'destroy'])
        ->name('api.answers.destroy');
    
    // Answer interaction API routes
    Route::post('answers/{answer}/toggle-like', [AnswerController::class, 'toggleLike'])
        ->name('api.answers.like');
    Route::post('answers/{answer}/comments', [AnswerController::class, 'addComment'])
        ->name('api.answers.comments.store');
    Route::get('answers/{answer}/comments', [AnswerController::class, 'getComments'])
        ->name('api.answers.comments.index');
    Route::post('answers/{answer}/toggle-acceptance', [AnswerController::class, 'toggleAcceptance'])
        ->name('api.answers.accept');
});
