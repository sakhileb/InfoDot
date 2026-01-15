<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Questions;

class QuestionCrud extends Component
{
    public Questions $model;
    public ?Questions $question = null;
    public bool $deleteQuestion = false;
    public bool $editQuestion = false;

    protected function rules(): array
    {
        return [
            'question.question' => 'required|min:3|max:255',
            'question.description' => 'required|min:3',
        ];
    }

    public function mount(Questions $model): void
    {
        $this->model = $model;
        $this->question = $model;
    }

    public function render()
    {
        return view('livewire.question-crud');
    }

    /**
     * Question CRUD.
     */

    public function editQuestion(): void
    {
        $this->editQuestion = true;
    }

    public function eQuestion(Questions $question): mixed
    {
        $this->validate();

        $this->question->save();

        $this->editQuestion = false;

        $this->dispatch('question-updated', questionId: $question->id);

        return $this->redirect(route('questions.view', ['qid' => $question->id]), navigate: true);
    }

    public function deleteQuestion(): void
    {
        $this->deleteQuestion = true;
    }

    public function delQuestion(Questions $question): mixed
    {
        $question->delete();
        $this->deleteQuestion = false;
        
        $this->dispatch('question-deleted', questionId: $question->id);
        
        return $this->redirect(route('questions.index'), navigate: true);
    }
}
