<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Questions;
use App\Models\Solutions;
use Livewire\Component;

class Search extends Component
{
    public $query;

    public $users;

    public $solutions;

    public $questions;

    public $highlightIndex;



    public function mount()
    {
        $this->resetFilters('search');
    }

    public function resetFilters()
    {
        $this->query = '';
        $this->solutions = [];
        $this->questions = [];
        $this->highlightIndex = 0;
    }

    public function incrementHighlight()
    {
        if ($this->highlightIndex === count($this->solutions)) {
            $this->highlightIndex = 0;
            return;
        }
        elseif ($this->highlightIndex === count($this->questions)) {
            $this->highlightIndex = 0;
            return;
        }

        $this->highlightIndex++;

    }

    public function decrementHighlight()
    {
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->solutions);
            return;
        }
        if ($this->highlightIndex === 0) {
            $this->highlightIndex = count($this->questions);
            return;
        }

        $this->highlightIndex--;
    }

    public function updatedQuery()
    {
        // $this->users = User::where('name', 'like', '%' . $this->query . '%')->get();
        $results = [];
        $this->solutions = Solutions::where('solution_title', 'like', '%' . $this->query . '%')->where(function ($q) {
            $q->where('solution_description', 'like', '%' . $this->query . '%')->orWhere('tags', 'like', '%' . $this->query . '%');
        })->get();

        $this->questions = Questions::where('question', 'like', '%' . $this->query . '%')->orWhere('description', 'like', '%' . $this->query . '%')->get();

        $results = [
            'questions' => $this->questions,
            'solutions' => $this->solutions
        ];
    }

    public function render()
    {
        return view('livewire.search');
    }
}
