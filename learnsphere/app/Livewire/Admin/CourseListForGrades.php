<?php

namespace App\Livewire\Admin;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseListForGrades extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $sortBy = 'title';
    public $sortDirection = 'asc';

    protected $queryString = ['search', 'sortBy', 'sortDirection'];

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

    public function getCoursesProperty()
    {
        $user = auth()->user();

        $query = Course::query();

        // If user is instructor, only show their courses
        if ($user->hasRole('instructor') && !$user->hasRole('admin')) {
            $query->where('instructor_id', $user->id);
        }

        return $query->where('title', 'like', '%' . $this->search . '%')
                    ->orderBy($this->sortBy, $this->sortDirection)
                    ->paginate(10);
    }

    public function render()
    {
        return view('livewire.admin.course-list-for-grades', [
            'courses' => $this->courses,
        ]);
    }
}
