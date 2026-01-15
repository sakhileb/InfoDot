<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\User;
use App\Models\Associates as AssociatesModel;
use Illuminate\Database\Eloquent\Model;

class Associates extends Component
{
    public User $user;
    public Model $model;

    public function mount(User $user, Model $model): void
    {
        $this->user = $user;
        $this->model = $model;
    }

    public function connect(): ?AssociatesModel
    {
        if (!Auth::check()) {
            $this->redirect(route('login'));
            return null;
        }

        $connect = AssociatesModel::where('user_id', Auth::id())
            ->where('associate_id', $this->user->id)
            ->whereNull('deleted_at')
            ->first();

        if ($connect) {
            $connect->delete();
            $this->dispatch('associate-disconnected', userId: $this->user->id);
        } else {
            $connect = AssociatesModel::create([
                'user_id' => Auth::id(),
                'associate_id' => $this->user->id
            ]);
            $this->dispatch('associate-connected', userId: $this->user->id);
        }

        return $connect;
    }

    public function render()
    {
        return view('livewire.associates');
    }
}
