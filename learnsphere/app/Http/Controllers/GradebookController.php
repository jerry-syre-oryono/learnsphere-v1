<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function index()
    {
        $courses = Course::with([
            'assignments.module',
            'students.submissions.submittable',
            'students.submissions.quiz',
            'enrollments' // Load enrollments to access student numbers
        ])->get();

        return view('admin.gradebook.index', compact('courses'));
    }
}
