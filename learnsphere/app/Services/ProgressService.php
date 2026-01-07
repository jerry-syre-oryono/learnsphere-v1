<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class ProgressService
{
    protected FinalGradeService $finalGradeService;

    public function __construct(FinalGradeService $finalGradeService)
    {
        $this->finalGradeService = $finalGradeService;
    }

    public function markLessonAsComplete(User $user, Lesson $lesson): void
    {
        $user->completedLessons()->syncWithoutDetaching($lesson->id);
    }

    public function getOverallCourseProgress(User $user, Course $course): float
    {
        return $this->finalGradeService->calculateFinalGrade($user, $course);
    }

    /**
     * Backwards-compatible wrapper used by some components.
     */
    public function getCourseCompletionPercentage(User $user, Course $course): float
    {
        return $this->getOverallCourseProgress($user, $course);
    }
}
