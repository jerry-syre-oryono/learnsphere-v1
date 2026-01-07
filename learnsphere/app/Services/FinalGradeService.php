<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\Assignment;
use App\Models\Assessment;

class FinalGradeService
{
    public function calculateFinalGrade(User $user, Course $course): float
    {
        $finalGrade = 0;

        $assessableItems = $course->getAssessableItemsAttribute();

        foreach ($assessableItems as $item) {
            $submission = null;
            if ($item instanceof Assessment) {
                $submission = Submission::where('user_id', $user->id)
                    ->where('submittable_type', Assessment::class)
                    ->where('submittable_id', $item->id)
                    ->latest()
                    ->first();

                if ($submission) {
                    $finalGrade += ($submission->percentage / 100) * $item->weight;
                }
            } elseif ($item instanceof Assignment) {
                $submission = Submission::where('user_id', $user->id)
                    ->where('submittable_type', Assignment::class)
                    ->where('submittable_id', $item->id)
                    ->latest()
                    ->first();

                if ($submission && $item->max_score > 0) {
                    $percentage = ($submission->score / $item->max_score) * 100;
                    $finalGrade += ($percentage / 100) * $item->weight;
                }
            }
        }

        return round($finalGrade, 2);
    }
}
