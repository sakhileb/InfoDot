<?php

namespace App\Http\Controllers\Solutions;

use App\Models\Steps;
use App\Models\Solutions;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\EagerLoadingOptimizer;
use App\Http\Requests\StoreSolutionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SolutionsController extends Controller
{
    use EagerLoadingOptimizer;

    /**
     * Display a listing of solutions
     */
    public function index(): View
    {
        return view('solutions.index');
    }

    /**
     * Show the form for creating a new solution
     */
    public function create(): View
    {
        return view('solutions.create');
    }

    /**
     * Store a newly created solution in storage
     */
    public function add_solution(StoreSolutionRequest $request): RedirectResponse
    {
        $solution = new Solutions();
        $solution->user_id = auth()->id();
        $solution->solution_title = $request->input('solution_title');
        $solution->solution_description = $request->input('solution_description');
        $solution->tags = $request->input('tags-input', $request->input('tags'));
        $solution->duration = $request->input('duration');
        $solution->duration_type = $request->input('duration_type');
        $solution->steps = $request->input('steps');
        $solution->save();

        // Handle file attachments if present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $solution->addMedia($file)
                    ->toMediaCollection('attachments');
            }
        }

        // Handle image uploads if present
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $solution->addMedia($image)
                    ->toMediaCollection('images');
            }
        }

        // Handle video uploads if present
        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $solution->addMedia($video)
                    ->toMediaCollection('videos');
            }
        }

        // Create steps for the solution
        $headings = $request->input('solution_heading');
        $bodies = $request->input('solution_body');

        foreach ($headings as $key => $heading) {
            $solution_step = new Steps();
            $solution_step->user_id = auth()->id();
            $solution_step->solution_id = $solution->id;
            $solution_step->solution_heading = $heading;
            $solution_step->solution_body = $bodies[$key] ?? '';
            $solution_step->save();
        }

        return redirect()->route('solutions.index')
            ->with('success', 'Solution created successfully!');
    }

    /**
     * Display the specified solution
     */
    public function view_solution(int $id): View
    {
        // Use optimized query with eager loading
        $solution = $this->getOptimizedSolutionsQuery()
            ->with(['comments.user']) // Add comments for detailed view
            ->where('id', $id)
            ->firstOrFail();
        
        return view('solutions.view')->with(['solution' => $solution]);
    }
}
