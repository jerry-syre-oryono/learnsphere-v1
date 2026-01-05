<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class GradebookController extends Controller
{
    public function index()
    {
        $courses = Course::with(['students.submissions.quiz'])->get();

        return view('admin.gradebook.index', compact('courses'));
    }
}
