<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Questions;
use App\Models\Solutions;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Search extends Component
{
    public string $query = '';
    public array $users = [];
    public array $solutions = [];
    public array $questions = [];
    public int $highlightIndex = 0;

    public function mount(): void
    {
        $this->resetFilters();
    }

    public function resetFilters(): void
    {
        $this->query = '';
        $this->solutions = [];
        $this->questions = [];
        $this->users = [];
        $this->highlightIndex = 0;
    }

    public function incrementHighlight(): void
    {
        $totalResults = count($this->solutions) + count($this->questions);
        
        if ($this->highlightIndex >= $totalResults - 1) {
            $this->highlightIndex = 0;
            return;
        }

        $this->highlightIndex++;
    }

    public function decrementHighlight(): void
    {
        $totalResults = count($this->solutions) + count($this->questions);
        
        if ($this->highlightIndex <= 0) {
            $this->highlightIndex = $totalResults - 1;
            return;
        }

        $this->highlightIndex--;
    }

    public function updatedQuery(): void
    {
        if (empty($this->query)) {
            $this->resetFilters();
            return;
        }

        // Search solutions using Scout with FULLTEXT fallback
        $this->solutions = $this->searchModel(Solutions::class, $this->query, 5);

        // Search questions using Scout with FULLTEXT fallback
        $this->questions = $this->searchModel(Questions::class, $this->query, 5);

        // Optionally search users using Scout with FULLTEXT fallback
        // $this->users = $this->searchModel(User::class, $this->query, 5);

        $this->highlightIndex = 0;
    }

    /**
     * Search a model using Scout if available, otherwise fallback to MySQL FULLTEXT.
     *
     * @param string $modelClass
     * @param string $query
     * @param int $limit
     * @return array
     */
    protected function searchModel(string $modelClass, string $query, int $limit): array
    {
        try {
            // Try to use Scout first
            if (config('scout.driver') !== null && config('scout.driver') !== 'null') {
                $results = $modelClass::search($query)
                    ->take($limit)
                    ->get();
                
                // Load relationships after Scout search
                if ($modelClass === Questions::class || $modelClass === Solutions::class) {
                    $results->load('user');
                }
                
                return $results->toArray();
            }
        } catch (\Exception $e) {
            // Scout not available or error occurred, fall through to FULLTEXT
            \Log::debug('Scout search failed, falling back to FULLTEXT: ' . $e->getMessage());
        }

        // Fallback to MySQL FULLTEXT search
        $results = $modelClass::query()
            ->searchFulltext($query)
            ->with($modelClass === Questions::class || $modelClass === Solutions::class ? 'user' : [])
            ->limit($limit)
            ->get();

        return $results->toArray();
    }

    #[Computed]
    public function hasResults(): bool
    {
        return !empty($this->solutions) || !empty($this->questions) || !empty($this->users);
    }

    #[Computed]
    public function totalResults(): int
    {
        return count($this->solutions) + count($this->questions) + count($this->users);
    }

    public function render()
    {
        return view('livewire.search');
    }
}
