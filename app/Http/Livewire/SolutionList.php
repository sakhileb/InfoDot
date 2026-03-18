<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Solutions;
use Illuminate\Pagination\LengthAwarePaginator;

class SolutionList extends Component
{
    public $perPage = 2;

    public $solutionsCollection;

    public $page = 1;

    public function mount()
    {
        $this->solutionsCollection = Solutions::latest()->take($this->perPage)->get();
    }

    public function loadMore()
    {
        $solutions = Solutions::latest()->take($this->perPage)
        ->skip((($this->page - 1) * $this->perPage) + $this->perPage)
        ->get();
        $this->solutionsCollection->push(...$solutions);

        $this->page++;
    }

    public function render()
    {
        $solutions = new LengthAwarePaginator($this->solutionsCollection, Solutions::count(), $this->perPage, $this->page);

        return view('livewire.solution-list', [
            'solutions' => $solutions
        ]);
    }
}
