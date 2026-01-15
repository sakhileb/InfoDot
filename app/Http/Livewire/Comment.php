<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Comment as CommentModel;

class Comment extends Component
{
    public CommentModel $comment;

    public function mount(CommentModel $comment): void
    {
        $this->comment = $comment;
    }

    public function render()
    {
        return view('livewire.comment');
    }
}
