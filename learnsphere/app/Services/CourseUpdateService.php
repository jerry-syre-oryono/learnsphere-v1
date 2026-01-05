<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseUpdateService
{
    public function execute(Course $course, array $data)
    {
        return DB::transaction(function () use ($course, $data) {
            // 1. Update Course details
            $courseFields = $data['course'];
            $courseFields['published'] = $courseFields['published'] ?? 0;
            $course->update($courseFields);

            $processedModuleIds = [];

            // 2. Sync Modules
            if (isset($data['modules'])) {
                foreach ($data['modules'] as $moduleData) {
                    $module = null;
                    if (isset($moduleData['id'])) {
                        $module = $course->modules()->find($moduleData['id']);
                    }

                    if ($module) {
                        $module->update([
                            'title' => $moduleData['title'],
                            'description' => $moduleData['description'] ?? null,
                            'order' => $moduleData['order'],
                        ]);
                    } else {
                        $module = $course->modules()->create([
                            'title' => $moduleData['title'],
                            'description' => $moduleData['description'] ?? null,
                            'order' => $moduleData['order'],
                        ]);
                    }
                    $processedModuleIds[] = $module->id;

                    $this->syncLessons($course, $module, $moduleData['lessons'] ?? []);
                    $this->syncAssignments($module, $moduleData['assignments'] ?? []);
                }
            }

            // Optional: Delete modules not in the request
            // $course->modules()->whereNotIn('id', $processedModuleIds)->delete();

            return $course;
        });
    }

    protected function syncLessons(Course $course, Module $module, array $lessonsData)
    {
        $processedLessonIds = [];
        foreach ($lessonsData as $lessonData) {
            $lesson = null;
            if (isset($lessonData['id'])) {
                $lesson = $module->lessons()->find($lessonData['id']);
            }

            $lessonFields = [
                'course_id' => $course->id,
                'title' => $lessonData['title'],
                'content_type' => $lessonData['content_type'] ?? 'text',
                'content' => $lessonData['content'] ?? null,
                'video_url' => $lessonData['video_url'] ?? null,
                'order' => $lessonData['order'],
                'attachment_name' => $lessonData['attachment_name'] ?? null,
            ];

            if (isset($lessonData['attachment']) && $lessonData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old if exists
                if ($lesson && $lesson->attachment_path) {
                    Storage::disk('public')->delete($lesson->attachment_path);
                }

                $file = $lessonData['attachment'];
                $baseName = $lessonData['attachment_name'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = Str::slug($baseName) . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();

                $lessonFields['attachment_path'] = $file->storeAs('attachments', $safeName, 'public');
            }

            if ($lesson) {
                $lesson->update($lessonFields);
            } else {
                $lesson = $module->lessons()->create($lessonFields);
            }
            $processedLessonIds[] = $lesson->id;
        }

        // $module->lessons()->whereNotIn('id', $processedLessonIds)->delete();
    }

    protected function syncAssignments(Module $module, array $assignmentsData)
    {
        foreach ($assignmentsData as $assignmentData) {
            $assignment = null;
            if (isset($assignmentData['id'])) {
                $assignment = $module->assignments()->find($assignmentData['id']);
            }

            $fields = [
                'title' => $assignmentData['title'],
                'description' => $assignmentData['description'] ?? null,
                'due_date' => $assignmentData['due_date'] ?? null,
                'max_score' => $assignmentData['max_score'] ?? 100,
                'attachment_name' => $assignmentData['attachment_name'] ?? null,
            ];

            if (isset($assignmentData['attachment']) && $assignmentData['attachment'] instanceof \Illuminate\Http\UploadedFile) {
                // Delete old if exists
                if ($assignment && $assignment->attachment_path) {
                    Storage::disk('public')->delete($assignment->attachment_path);
                }

                $file = $assignmentData['attachment'];
                $baseName = $assignmentData['attachment_name'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = 'assignment-' . Str::slug($baseName) . '-' . Str::random(6) . '.' . $file->getClientOriginalExtension();

                $fields['attachment_path'] = $file->storeAs('assignments', $safeName, 'public');
            }

            if ($assignment) {
                $assignment->update($fields);
            } else {
                $module->assignments()->create($fields);
            }
        }
    }
}
