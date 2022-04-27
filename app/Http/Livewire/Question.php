<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Question extends Component
{
    public $model;

    public $question;

    public function storeLike()
    {
        $like = $this->model->likes()->first();

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

    }

    public function render()
    {
        return view('livewire.question');
    }
}
