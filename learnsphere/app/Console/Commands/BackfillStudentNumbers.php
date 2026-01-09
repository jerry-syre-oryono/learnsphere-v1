<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Services\StudentNumberService;
use Illuminate\Console\Command;

class BackfillStudentNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-student-numbers {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill student numbers for existing enrollments that don\'t have them yet';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $studentNumberService = app(StudentNumberService::class);
        $isDryRun = $this->option('dry-run');

        // Find all enrollments without student numbers
        $enrollmentsWithoutNumbers = Enrollment::with(['user', 'course'])
            ->whereNull('student_number')
            ->get();

        if ($enrollmentsWithoutNumbers->isEmpty()) {
            $this->info('No enrollments found that need student numbers. All enrollments already have student numbers.');
            return;
        }

        $this->info("Found {$enrollmentsWithoutNumbers->count()} enrollments that need student numbers.");
        $this->info('');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
            $this->info('');
        }

        $processed = 0;
        $errors = 0;

        // Group by course and year for better processing and reporting
        $groupedEnrollments = $enrollmentsWithoutNumbers->groupBy(function ($enrollment) {
            return $enrollment->course_id . '-' . ($enrollment->enrollment_year ?? now()->year);
        });

        foreach ($groupedEnrollments as $groupKey => $enrollments) {
            [$courseId, $year] = explode('-', $groupKey);
            $course = $enrollments->first()->course;

            $this->info("Processing course: {$course->title} (ID: {$courseId}) for year {$year}");

            foreach ($enrollments as $enrollment) {
                try {
                    if (!$isDryRun) {
                        $studentNumber = $studentNumberService->generateStudentNumber(
                            $enrollment->user,
                            $enrollment->course
                        );
                    } else {
                        // For dry run, simulate the generation
                        $studentNumber = $enrollment->course->getCourseCode() . '-S-' . $year . '-XXX';
                    }

                    $this->line("  - {$enrollment->user->name} ({$enrollment->user->email}): {$studentNumber}");
                    $processed++;

                } catch (\Exception $e) {
                    $this->error("  - Failed for {$enrollment->user->name}: {$e->getMessage()}");
                    $errors++;
                }
            }

            $this->info('');
        }

        $this->info("Backfill completed!");
        $this->info("Processed: {$processed} enrollments");

        if ($errors > 0) {
            $this->error("Errors: {$errors} enrollments failed");
        }

        if ($isDryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        }
    }
}
