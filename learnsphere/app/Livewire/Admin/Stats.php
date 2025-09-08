<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;

class Stats extends Component
{
    public $userCount;

    public function mount()
    {
        $this->userCount = User::count();
    }

    public function render()
    {
        return view('livewire.admin.stats');
    }
}