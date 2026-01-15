<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Questions;
use App\Http\Controllers\Traits\EagerLoadingOptimizer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class QuestionList extends Component
{
    use EagerLoadingOptimizer;

    public int $perPage = 10;
    public Collection $questionsCollection;
    public int $page = 1;

    public function mount(): void
    {
        // Use optimized query with eager loading
        $this->questionsCollection = $this->getOptimizedQuestionsQuery()
            ->latest()
            ->take($this->perPage)
            ->get();
    }

    public function loadMore(): void
    {
        // Use optimized query with eager loading
        $questions = $this->getOptimizedQuestionsQuery()
            ->latest()
            ->take($this->perPage)
            ->skip((($this->page - 1) * $this->perPage) + $this->perPage)
            ->get();
        
        $this->questionsCollection->push(...$questions);
        $this->page++;
    }

    #[On('question-created')]
    public function refreshQuestions(): void
    {
        // Reload the first page of questions when a new question is created
        $this->questionsCollection = $this->getOptimizedQuestionsQuery()
            ->latest()
            ->take($this->perPage * $this->page)
            ->get();
    }

    #[On('echo:questions,QuestionWasAsked')]
    public function handleNewQuestion($event): void
    {
        // Real-time listener for new questions via broadcasting
        $this->refreshQuestions();
    }

    public function render()
    {
        $totalQuestions = Questions::count();
        $questions = new LengthAwarePaginator(
            $this->questionsCollection,
            $totalQuestions,
            $this->perPage,
            $this->page
        );

        return view('livewire.question-list', [
            'questions' => $questions
        ]);
    }
}
