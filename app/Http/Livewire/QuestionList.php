<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Questions;
use Illuminate\Pagination\LengthAwarePaginator;

class QuestionList extends Component
{
    public $perPage = 2;

    public $questionsCollection;

    public $page = 1;

    public function mount()
    {
        $this->questionsCollection = Questions::latest()->take($this->perPage)->get();
    }

    public function loadMore()
    {
        $questions = Questions::latest()->take($this->perPage)
        ->skip((($this->page - 1) * $this->perPage) + $this->perPage)
        ->get();
        $this->questionsCollection->push(...$questions);

        $this->page++;
    }

    public function render()
    {
        $questions = new LengthAwarePaginator($this->questionsCollection, Questions::count(), $this->perPage, $this->page);

        return view('livewire.question-list', [
            'questions' => $questions
        ]);
    }
}
