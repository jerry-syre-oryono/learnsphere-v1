<?php

namespace App\Livewire\Admin;

use App\Models\Enrollment;
use App\Models\StudentCourseResult;
use App\Services\Grading\GradingService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Grade Book Component
 *
 * Admin interface for viewing and managing grades.
 * Allows bulk grade entry and corrections.
 */
class GradeBook extends Component
{
    use WithPagination;

    public $course;
    public $semester = '';
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search', 'sortBy', 'sortDirection'];

    public function mount($course)
    {
        $this->course = $course;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sort($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCourseResultsProperty()
    {
        return StudentCourseResult::where('course_id', $this->course->id)
            ->with(['enrollment.user', 'course'])
            ->when($this->semester, fn ($q) => $q->where('semester', $this->semester))
            ->when($this->search, fn ($q) => $q->whereHas('enrollment.user', fn ($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
            ))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.admin.grade-book');
    }
}
