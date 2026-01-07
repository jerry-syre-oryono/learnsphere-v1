<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Submission;
use App\Models\User;
use App\Services\FinalGradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalGradeServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_the_final_grade_correctly()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        
        $assessment = Assessment::factory()->create([
            'assessable_id' => $module->id,
            'assessable_type' => Module::class,
            'weight' => 60,
        ]);

        $assignment = Assignment::factory()->create([
            'module_id' => $module->id,
            'weight' => 40,
            'max_score' => 100,
        ]);

        Submission::factory()->create([
            'user_id' => $user->id,
            'quiz_id' => $assessment->id,
            'percentage' => 80, // 80% on the assessment
        ]);

        AssignmentSubmission::factory()->create([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'score' => 90, // 90 out of 100 on the assignment
        ]);

        $finalGradeService = new FinalGradeService();
        $finalGrade = $finalGradeService->calculateFinalGrade($user, $course);

        // Expected grade: (80% * 0.60) + (90% * 0.40) = 48 + 36 = 84
        $this->assertEquals(84, $finalGrade);
    }
}