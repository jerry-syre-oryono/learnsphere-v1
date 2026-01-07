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

        $course->load(['modules.lessons.assessments', 'modules.assignments']);

        $modulesData = $course->modules->map(function ($m) {
            return [
                'id' => $m->id,
                'title' => $m->title,
                'description' => $m->description,
                'order' => $m->order,
                'expanded' => true,
                'lessons' => $m->lessons->map(function ($l) {
                    $lessonData = [
                        'id' => $l->id,
                        'title' => $l->title,
                        'content' => $l->content,
                        'content_type' => $l->content_type ?? 'text',
                        'video_url' => $l->video_url,
                        'order' => $l->order,
                        'attachment_path' => $l->attachment_path,
                        'attachment_name' => $l->attachment_name,
                        'assessment' => null,
                    ];

                    if ($l->content_type === 'quiz' && $l->assessments->isNotEmpty()) {
                        $assessment = $l->assessments->first();
                        $lessonData['assessment'] = [
                            'id' => $assessment->id,
                            'weight' => $assessment->weight,
                        ];
                    }

                    return $lessonData;
                }),
                'assignments' => $m->assignments->map(function ($a) {
                    return [
                        'id' => $a->id,
                        'title' => $a->title,
                        'description' => $a->description,
                        'max_score' => $a->max_score,
                        'due_date' => $a->due_date ? $a->due_date->format('Y-m-d') : null,
                        'attachment_path' => $a->attachment_path,
                        'attachment_name' => $a->attachment_name,
                        'weight' => $a->weight,
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
