<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\Questions;
use Illuminate\Database\Eloquent\Model;

class Question extends Component
{
    public Model $model;
    public ?Questions $question = null;

    public function mount(Model $model): void
    {
        $this->model = $model;
        if ($model instanceof Questions) {
            $this->question = $model;
        }
    }

    public function storeLike(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $like = $this->model->likes()->where('user_id', Auth::id())->first();

        if ($like) {
            $like->delete();
            $this->dispatch('question-unliked', questionId: $this->model->id);
        } else {
            $like = $this->model->likes()->make();
            $like->user()->associate(auth()->user());
            $like->save();
            $this->dispatch('question-liked', questionId: $this->model->id);
        }
    }

    public function markedAsSolved(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        // Check if user is the question author
        if ($this->model->user_id !== Auth::id()) {
            session()->flash('error', 'Only the question author can mark it as solved');
            return;
        }

        $this->model->update(['status' => 1]);
        
        $this->dispatch('question-marked-solved', questionId: $this->model->id);
        
        session()->flash('success', 'Question marked as solved');
    }

    public function render()
    {
        return view('livewire.question');
    }
}
