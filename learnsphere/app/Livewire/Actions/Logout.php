<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

class Logout extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function handle(): void
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        // Temporarily return a simple response to debug "View [] not found"
        $this->redirect(route('home'));
    }
}