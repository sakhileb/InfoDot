<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Solutions\SolutionsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    // Dashboard Pages

    // // -------------------------------------------------------
    // Solutions Controller
    // -------------------------------------------------------
    Route::get('/solutions', 'App\Http\Controllers\Solutions\SolutionsController@index')->name('solutions');
    Route::get('/solution/create', function () { return view('solutions.create');})->name('create_solution');
    Route::post('/solution/add', 'App\Http\Controllers\Solutions\SolutionsController@add_solution')->name('solutions.add');
    Route::get('/solution/view/{id}', 'App\Http\Controllers\Solutions\SolutionsController@view_solution')->name('solutions.view');

    // -------------------------------------------------------
    // Question Controller
    // -------------------------------------------------------
    Route::get('/questions', 'App\Http\Controllers\Questions\QuestionsController@index')->name('questions');
    Route::get('/questions/ask', 'App\Http\Controllers\Questions\QuestionsController@seek')->name('seek');
    Route::post('/questions/add', 'App\Http\Controllers\Questions\QuestionsController@add_question')->name('questions.add');
    Route::get('/question/view/{qid}', 'App\Http\Controllers\Questions\QuestionsController@view')->name('questions.view');

});

// -------------------------------------------------------
// Outside Pages
// -------------------------------------------------------
Route::get('/home', 'App\Http\Controllers\HomeController@index')->name('home');
Route::get('/about', 'App\Http\Controllers\PagesController@about')->name('about');
Route::get('/contact', 'App\Http\Controllers\PagesController@contact')->name('contact');
Route::get('/solution-results', 'App\Http\Controllers\PagesController@solution_search_results')->name('solution_search_results');

// ===============================================================













