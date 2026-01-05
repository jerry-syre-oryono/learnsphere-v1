<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class CourseCreationService
{
    public function execute(array $data, $instructorId)
    {
        return DB::transaction(function () use ($data, $instructorId) {
            $courseData = $data['course'];
            $courseData['instructor_id'] = $instructorId;
            $courseData['slug'] = Str::slug($courseData['title']) . '-' . Str::random(6);

            $course = Course::create($courseData);

            if (isset($data['modules'])) {
                foreach ($data['modules'] as $moduleData) {
                    $this->createModule($course, $moduleData);
                }
            }

            return $course;
        });
    }

    protected function createModule(Course $course, array $moduleData)
    {
        $module = $course->modules()->create([
            'title' => $moduleData['title'],
            'description' => $moduleData['description'] ?? null,
            'order' => $moduleData['order'],
        ]);

        if (isset($moduleData['lessons'])) {
            foreach ($moduleData['lessons'] as $lessonData) {
                $module->lessons()->create([
                    'course_id' => $course->id, // Redundant if using proper relations but good for safety
                    'title' => $lessonData['title'],
                    'content' => $lessonData['content'] ?? null,
                    'video_url' => $lessonData['video_url'] ?? null,
                    'order' => $lessonData['order'],
                ]);
            }
        }

        if (isset($moduleData['assignments'])) {
            foreach ($moduleData['assignments'] as $assignmentData) {
                Assignment::create([
                    'module_id' => $module->id,
                    'title' => $assignmentData['title'],
                    'description' => $assignmentData['description'] ?? null,
                    'due_date' => $assignmentData['due_date'] ?? null,
                    'max_score' => $assignmentData['max_score'] ?? 100,
                ]);
            }
        }
    }
}
