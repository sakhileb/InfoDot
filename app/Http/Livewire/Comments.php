<?php

namespace App\Http\Livewire;

use Auth;
use Livewire\Component;

class Comments extends Component
{
    public $model;

    public $question;

    public $solution;

    public $newCommentState = [
        'body' => '',
        'status' => 0
    ];

    protected $validationAttributes = [
        'newCommentState.body' => 'comment'
    ];

    public function storeLike()
    {
        $like = $this->model->likes()->where('user_id', Auth::id())->first();

        if($like)
        {
            $like->delete();
        }
        else
        {
            $like = $this->model->likes()->make();
            $like->user()->associate(auth()->user());
            $like->save();
        }
    }

    public function markedAsSolved()
    {
        $solved = $this->model->update(['status' => 1]);
        $this->newCommentState = [
            'status' => 1
        ];
    }

    public function postComment()
    {
        $this->validate([
            'newCommentState.body' => 'required'
        ]);

        $comment = $this->model->comments()->make($this->newCommentState);
        $comment->user()->associate(auth()->user());

        $comment->save();

        $this->newCommentState = [
            'body' => ''
        ];
    }

    public function render()
    {
        $comments = $this->model->comments()->with('user', 'children.user', 'children.children')->parent()->latest()->get();

        return view('livewire.comments', [
            'comments' => $comments
        ]);
    }
}
