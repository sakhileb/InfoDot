<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Solutions\SolutionsController;
use App\Http\Controllers\Questions\QuestionsController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// -------------------------------------------------------
// Public Solutions Routes
// -------------------------------------------------------
Route::get('/solutions', [SolutionsController::class, 'index'])->name('solutions.index');
Route::get('/solutions/{id}', [SolutionsController::class, 'view_solution'])->name('solutions.view');

// -------------------------------------------------------
// Public Questions Routes
// -------------------------------------------------------
Route::get('/questions', [QuestionsController::class, 'index'])->name('questions.index');
Route::get('/questions/{qid}', [QuestionsController::class, 'view'])->name('questions.view');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // -------------------------------------------------------
    // Authenticated Solutions Routes
    // -------------------------------------------------------
    Route::get('/solutions/create', [SolutionsController::class, 'create'])->name('solutions.create');
    Route::post('/solution/add', [SolutionsController::class, 'add_solution'])
        ->name('solutions.add')
        ->middleware('throttle:10,1'); // 10 solutions per minute

    // -------------------------------------------------------
    // Authenticated Questions Routes
    // -------------------------------------------------------
    Route::get('/questions/seek', [QuestionsController::class, 'seek'])->name('questions.seek');
    Route::post('/questions/add', [QuestionsController::class, 'add_question'])
        ->name('questions.add')
        ->middleware('throttle:10,1'); // 10 questions per minute

    // -------------------------------------------------------
    // Answer Routes
    // -------------------------------------------------------
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store'])
        ->name('answers.store')
        ->middleware('throttle:20,1'); // 20 answers per minute
    Route::delete('/answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');
    Route::get('/questions/{question}/answers', [AnswerController::class, 'byQuestion'])->name('questions.answers');
    
    // Answer interaction routes
    Route::post('/answers/{answer}/like', [AnswerController::class, 'toggleLike'])
        ->name('answers.like')
        ->middleware('throttle:30,1'); // 30 likes per minute
    Route::post('/answers/{answer}/comments', [AnswerController::class, 'addComment'])
        ->name('answers.comments.store')
        ->middleware('throttle:20,1'); // 20 comments per minute
    Route::get('/answers/{answer}/comments', [AnswerController::class, 'getComments'])->name('answers.comments.index');
    Route::post('/answers/{answer}/accept', [AnswerController::class, 'toggleAcceptance'])->name('answers.accept');

    // -------------------------------------------------------
    // Profile Routes
    // -------------------------------------------------------
    Route::get('/user/profile/edit', [PagesController::class, 'edit'])->name('profile.edit');
    Route::get('/user/profile/{id}', [PagesController::class, 'show'])->name('profile.show');

    // -------------------------------------------------------
    // File and Folder Routes
    // -------------------------------------------------------
    Route::resource('files', \App\Http\Controllers\FileController::class)
        ->except(['show', 'edit', 'update'])
        ->middleware('throttle:20,1'); // 20 file operations per minute
    Route::get('/files/{file}/download', [\App\Http\Controllers\FileController::class, 'download'])
        ->name('files.download')
        ->middleware('throttle:30,1'); // 30 downloads per minute
    
    Route::resource('folders', \App\Http\Controllers\FolderController::class)
        ->except(['show', 'edit'])
        ->middleware('throttle:20,1'); // 20 folder operations per minute
});

// -------------------------------------------------------
// Public Routes
// -------------------------------------------------------
Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/about', [PagesController::class, 'about'])->name('about');
Route::get('/contact', [PagesController::class, 'contact'])->name('contact');
Route::post('/contact-send', [PagesController::class, 'contactSend'])
    ->name('send-contact')
    ->middleware('throttle.contact');
Route::get('/faqs', [PagesController::class, 'faqs'])->name('faqs');
Route::get('/complains', [PagesController::class, 'complains'])->name('complains');
Route::get('/terms', [PagesController::class, 'terms'])->name('terms');
Route::get('/policy', [PagesController::class, 'policy'])->name('policy');
Route::get('/solution-results', [PagesController::class, 'solution_search_results'])->name('solution_search_results');

// Demo page for answer interactions
Route::get('/answer-interactions-demo', function () {
    return view('answer-interactions-demo');
})->name('answer.interactions.demo');
