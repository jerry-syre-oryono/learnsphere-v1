<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\StudentCourseResult;
use App\Models\ProgramLevel;
use App\Services\Grading\GradingService;
use Illuminate\Database\Seeder;

class StudentGradesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the GradingService
        $gradingService = app(GradingService::class);

        // Get approved student users
        $students = User::query()
            ->where('is_approved', true)
            ->whereHas('roles', function($q) {
                $q->where('name', 'student');
            })
            ->get();

        $this->command->info("Found " . $students->count() . " approved student users");

        // Define sample grades (percentage marks between 0-100)
        $sampleMarks = [85, 78, 92, 65, 45, 88, 72, 55, 95, 70];
        $markIndex = 0;

        foreach ($students as $student) {
            // Get student's enrollments
            $enrollments = $student->enrollments()->with('course', 'programLevel')->get();

            if ($enrollments->isEmpty()) {
                $this->command->warn("  ⚠ {$student->name}: No enrollments found");
                continue;
            }

            $this->command->info("Processing {$student->name}:");

            foreach ($enrollments as $enrollment) {
                // Skip if enrollment doesn't have a program level
                if (!$enrollment->programLevel) {
                    $this->command->warn("    - Skipping (no program level)");
                    continue;
                }

                // Get a sample mark
                $mark = $sampleMarks[$markIndex % count($sampleMarks)];
                $markIndex++;

                try {
                    // Process the grade using the GradingService
                    $result = $gradingService->processStudentGrade(
                        $enrollment,
                        $mark,
                        3.0, // credit units
                        false, // not a retake
                        'fall-2025' // semester
                    );

                    $this->command->line("    ✓ {$enrollment->course->name}: {$mark}% → {$result->letter_grade} ({$result->grade_point} points)");
                } catch (\Exception $e) {
                    $this->command->error("    ✗ {$enrollment->course->name}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("\n✅ Student grades seeding completed!");
    }
}
