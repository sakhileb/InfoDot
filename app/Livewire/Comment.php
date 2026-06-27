<?php

namespace App\Livewire;

use Livewire\Component;

class Comment extends Component
{
    public mixed $comment;

    public function render(): \Illuminate\View\View
    {
        return view('livewire.comment');
    }
}
