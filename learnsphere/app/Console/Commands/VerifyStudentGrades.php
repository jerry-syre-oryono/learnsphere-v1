<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\StudentCourseResult;
use App\Services\Grading\GPACalculator;
use App\Services\Grading\CGPACalculator;
use App\Services\Grading\ClassificationResolver;
use App\Services\Grading\AcademicStandingResolver;
use Illuminate\Console\Command;

class VerifyStudentGrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grades:verify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that all seeded student grades are calculated correctly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $gpaCalc = app(GPACalculator::class);
        $cgpaCalc = app(CGPACalculator::class);
        $classifyCalc = app(ClassificationResolver::class);
        $standingCalc = app(AcademicStandingResolver::class);

        $this->info('=== STUDENT GRADES VERIFICATION ===');

        // Get students with grades
        $students = User::query()
            ->where('is_approved', true)
            ->whereHas('roles', function($q) {
                $q->where('name', 'student');
            })
            ->get();

        if ($students->isEmpty()) {
            $this->warn('No approved student users found');
            return;
        }

        $allVerified = true;

        foreach ($students as $student) {
            $results = StudentCourseResult::query()
                ->whereHas('enrollment', function($q) use ($student) {
                    $q->where('user_id', $student->id);
                })
                ->get();

            if ($results->isEmpty()) {
                $this->error("\n❌ {$student->name}: No grades found");
                $allVerified = false;
                continue;
            }

            $this->line("\n📊 {$student->name}:");
            $this->line("   Courses: {$results->count()}");

            // Calculate GPA
            $gpa = $gpaCalc->calculateFromResults($results);
            $cgpa = $cgpaCalc->calculateFromResults($results);

            $this->line("   GPA: " . number_format($gpa, 2));
            $this->line("   CGPA: " . number_format($cgpa, 2));

            // Get classification
            $classification = $classifyCalc->resolve($cgpa, 'degree');
            $this->line("   Classification: {$classification['class']}");

            // Get academic standing
            $standing = $standingCalc->resolve($student, $cgpa);
            $this->line("   Standing: {$standing['status']}");

            // Show individual courses
            $this->line("   Courses:");
            foreach ($results as $result) {
                $cap = $result->was_capped ? " (⚠️ Capped from {$result->original_grade} to {$result->capped_grade})" : '';
                $this->line("     • {$result->course->name}: {$result->final_mark}% → {$result->letter_grade} ({$result->grade_point} pts, {$result->grade_points_earned} earned){$cap}");
            }

            // Verify manual calculation
            $totalPoints = $results->sum('grade_points_earned');
            $totalUnits = $results->sum('credit_units');
            $expectedGPA = round($totalPoints / $totalUnits, 2);
            $calculatedGPA = round($gpa, 2);
            $match = abs($expectedGPA - $calculatedGPA) < 0.01;

            if ($match) {
                $this->line("   Verification: ✓ (Expected GPA: {$expectedGPA}, Got: {$calculatedGPA})");
            } else {
                $this->error("   Verification: ✗ (Expected GPA: {$expectedGPA}, Got: {$calculatedGPA})");
                $allVerified = false;
            }
        }

        if ($allVerified) {
            $this->info("\n✅ All student grades verified successfully!");
        } else {
            $this->warn("\n⚠️ Some verifications failed. Check the output above.");
        }
    }
}
