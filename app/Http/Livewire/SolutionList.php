<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Solutions;
use App\Http\Controllers\Traits\EagerLoadingOptimizer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SolutionList extends Component
{
    use EagerLoadingOptimizer;

    public int $perPage = 10;
    public Collection $solutionsCollection;
    public int $page = 1;

    public function mount(): void
    {
        // Use optimized query with eager loading
        $this->solutionsCollection = $this->getOptimizedSolutionsQuery()
            ->latest()
            ->take($this->perPage)
            ->get();
    }

    public function loadMore(): void
    {
        // Use optimized query with eager loading
        $solutions = $this->getOptimizedSolutionsQuery()
            ->latest()
            ->take($this->perPage)
            ->skip((($this->page - 1) * $this->perPage) + $this->perPage)
            ->get();
        
        $this->solutionsCollection->push(...$solutions);
        $this->page++;
    }

    #[On('solution-created')]
    public function refreshSolutions(): void
    {
        // Reload the first page of solutions when a new solution is created
        $this->solutionsCollection = $this->getOptimizedSolutionsQuery()
            ->latest()
            ->take($this->perPage * $this->page)
            ->get();
    }

    #[On('echo:solutions,SolutionWasCreated')]
    public function handleNewSolution($event): void
    {
        // Real-time listener for new solutions via broadcasting
        $this->refreshSolutions();
    }

    public function render()
    {
        $totalSolutions = Solutions::count();
        $solutions = new LengthAwarePaginator(
            $this->solutionsCollection,
            $totalSolutions,
            $this->perPage,
            $this->page
        );

        return view('livewire.solution-list', [
            'solutions' => $solutions
        ]);
    }
}
