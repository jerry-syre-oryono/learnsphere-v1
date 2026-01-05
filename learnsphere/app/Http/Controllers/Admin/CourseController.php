<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Services\CourseCreationService;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CourseController extends Controller
{
    use AuthorizesRequests;
    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(StoreCourseRequest $request, CourseCreationService $service)
    {
        $validated = $request->validated();
        $course = $service->execute($validated, Auth::id());

        return redirect()->route('admin.dashboard')
            ->with('success', 'Course created successfully: ' . $course->title);
    }

    public function update(Course $course, StoreCourseRequest $request, \App\Services\CourseUpdateService $service)
    {
        $this->authorize('update', $course);

        $validated = $request->validated();
        $service->execute($course, $validated);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Course updated successfully.');
    }
}
