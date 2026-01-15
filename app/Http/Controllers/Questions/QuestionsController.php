<?php

namespace App\Http\Controllers\Questions;

use App\Models\Questions;
use App\Events\Questions\QuestionWasAsked;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\EagerLoadingOptimizer;
use App\Http\Requests\StoreQuestionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class QuestionsController extends Controller
{
    use EagerLoadingOptimizer;

    /**
     * Display a listing of questions
     */
    public function index(): View
    {
        return view('questions.index');
    }

    /**
     * Show the form for creating a new question
     */
    public function seek(): View
    {
        return view('questions.seek');
    }

    /**
     * Display the specified question
     */
    public function view(int $qid): View
    {
        // Use optimized query with eager loading
        $question = $this->getOptimizedQuestionsQuery()
            ->where('id', $qid)
            ->firstOrFail();
        
        return view('questions.view')->with(['question' => $question]);
    }

    /**
     * Store a newly created question in storage
     */
    public function add_question(StoreQuestionRequest $request): RedirectResponse
    {
        $question = new Questions();
        $question->user_id = auth()->id();
        $question->question = $request->input('question');
        $question->description = $request->input('description');
        $question->tags = $request->input('tags');
        $question->save();

        // Handle file attachments if present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $question->addMedia($file)
                    ->toMediaCollection('attachments');
            }
        }

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $question->addMedia($image)
                    ->toMediaCollection('images');
            }
        }

        // Dispatch event for real-time updates
        event(new QuestionWasAsked($question));

        return redirect()->route('questions.index')
            ->with('success', 'Question posted successfully!');
    }
}
