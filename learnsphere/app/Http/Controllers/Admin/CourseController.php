<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Services\CourseCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
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
}
