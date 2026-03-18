<?php

namespace App\Http\Livewire;

use Auth;
use Livewire\Component;

class Associates extends Component
{
    public $user;

    public $model;

    public function connect()
    {
        $connect = \App\Models\Associates::where('user_id', Auth::id())->where('associate_id', $this->user->id)->whereNull('deleted_at')->first();

        if($connect)
        {
            $connect->delete();
        }
        else
        {
            $connect = $this->model->associates()->make();
            $connect->create(['user_id' => Auth::id(), 'associate_id' => $this->user->id]);
        }

        return $connect;
    }

    public function render()
    {
        return view('livewire.associates');
    }
}
