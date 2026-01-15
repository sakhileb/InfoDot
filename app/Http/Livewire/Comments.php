<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Database\Eloquent\Model;

class Comments extends Component
{
    public Model $model;
    public ?object $question = null;
    public ?object $solution = null;

    public array $newCommentState = [
        'body' => '',
        'status' => 0
    ];

    protected array $validationAttributes = [
        'newCommentState.body' => 'comment'
    ];

    public function mount(Model $model): void
    {
        $this->model = $model;
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
        } else {
            $like = $this->model->likes()->make();
            $like->user()->associate(auth()->user());
            $like->save();
        }

        $this->dispatch('like-toggled', modelType: get_class($this->model), modelId: $this->model->id);
    }

    public function markedAsSolved(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->model->update(['status' => 1]);
        $this->newCommentState['status'] = 1;

        $this->dispatch('marked-as-solved', modelType: get_class($this->model), modelId: $this->model->id);
    }

    public function postComment(): void
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->validate([
            'newCommentState.body' => 'required|string|min:1|max:1000'
        ]);

        $comment = $this->model->comments()->make($this->newCommentState);
        $comment->user()->associate(auth()->user());
        $comment->save();

        $this->newCommentState = [
            'body' => ''
        ];

        $this->dispatch('comment-posted', modelType: get_class($this->model), modelId: $this->model->id);
    }

    public function render()
    {
        $comments = $this->model->comments()
            ->with('user', 'children.user', 'children.children')
            ->parent()
            ->latest()
            ->get();

        return view('livewire.comments', [
            'comments' => $comments
        ]);
    }
}
