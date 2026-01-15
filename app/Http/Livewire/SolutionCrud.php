<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Steps;
use App\Models\Solutions;

class SolutionCrud extends Component
{
    public Solutions $model;
    public ?Solutions $solution = null;
    public bool $deleteSolution = false;
    public bool $editSolution = false;

    protected function rules(): array
    {
        return [
            'solution.solution_title' => 'required|min:3|max:255',
            'solution.solution_description' => 'required|min:3',
            'solution.tags' => 'required|min:3',
            'solution.duration' => 'required',
            'solution.duration_type' => 'required',
            'solution.steps' => 'required'
        ];
    }

    public function mount(Solutions $model): void
    {
        $this->model = $model;
        $this->solution = $model;
    }

    public function render()
    {
        return view('livewire.solution-crud');
    }

    /**
     * Solution CRUD.
     */

    public function editSolution(): void
    {
        $this->editSolution = true;
    }

    public function eSolution(Solutions $solution): mixed
    {
        $this->validate();

        $this->solution->save();

        // Note: Step editing would need to be implemented separately
        // as it requires handling arrays of step data

        $this->editSolution = false;

        $this->dispatch('solution-updated', solutionId: $solution->id);

        return $this->redirect(route('solutions.view', ['id' => $solution->id]), navigate: true);
    }

    public function deleteSolution(): void
    {
        $this->deleteSolution = true;
    }

    public function delSolution(Solutions $solution): mixed
    {
        $solution->delete();
        $this->deleteSolution = false;
        
        $this->dispatch('solution-deleted', solutionId: $solution->id);
        
        return $this->redirect(route('solutions.index'), navigate: true);
    }
}
