<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CourseEditController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Course $course)
    {
        if (!Gate::allows('update', $course)) {
            abort(403);
        }

        $course->load(['modules.lessons', 'modules.assignments']);

        $modulesData = $course->modules->map(function ($m) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'description' => $m->description,
                'order' => $m->order,
                'expanded' => true,
                'lessons' => $m->lessons->map(function ($l) {
                    return [
                        'id' => $l->id,
                        'title' => $l->title,
                        'content' => $l->content,
                        'content_type' => $l->content_type ?? 'text',
                        'video_url' => $l->video_url,
                        'order' => $l->order,
                        'attachment_path' => $l->attachment_path,
                        'attachment_name' => $l->attachment_name
                    ];
                }),
                'assignments' => $m->assignments->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'max_score' => $a->max_score,
                        'due_date' => $a->due_date ? $a->due_date->format('Y-m-d') : null,
                        'attachment_path' => $a->attachment_path,
                        'attachment_name' => $a->attachment_name,
                    ];
                }) ?: []
            ];
        });

        return view('admin.courses.edit', [
            'course' => $course,
            'modulesData' => $modulesData,
        ]);
    }
}
