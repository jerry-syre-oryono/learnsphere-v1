<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class ProgressService
{
    public function markLessonAsComplete(User $user, Lesson $lesson): void
    {
        $user->completedLessons()->syncWithoutDetaching($lesson->id);
    }

    public function getCourseCompletionPercentage(User $user, Course $course): float
    {
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) {
            return 0.0;
        }

        $completedLessons = $user->completedLessons()
            ->where('lessons.course_id', $course->id)
            ->count();
            
        return round(($completedLessons / $totalLessons) * 100, 2);
    }
}
