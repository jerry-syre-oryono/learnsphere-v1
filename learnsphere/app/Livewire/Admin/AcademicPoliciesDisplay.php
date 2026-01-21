<?php

namespace App\Livewire\Admin;

use App\Models\AcademicPolicy;
use Livewire\Component;

/**
 * Academic Policies Display Component
 *
 * Displays NCHE-aligned academic policies and regulations.
 * Read-only information display.
 */
class AcademicPoliciesDisplay extends Component
{
    public array $policies = [];

    public function mount()
    {
        $this->policies = AcademicPolicy::where('is_active', true)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.academic-policies-display', [
            'policies' => $this->policies,
        ]);
    }
}
