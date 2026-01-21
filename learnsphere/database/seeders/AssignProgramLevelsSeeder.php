<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\ProgramLevel;
use Illuminate\Database\Seeder;

class AssignProgramLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create program levels
        $degreeLevel = ProgramLevel::firstOrCreate(
            ['code' => 'DEG'],
            [
                'name' => 'Degree',
                'description' => 'Bachelor Degree Program',
                'is_active' => true,
                'require_cgpa_for_graduation' => true,
            ]
        );

        // Get all enrollments without a program level
        $enrollmentsToUpdate = Enrollment::whereNull('program_level_id')->get();

        $this->command->info("Found " . $enrollmentsToUpdate->count() . " enrollments without program level");

        foreach ($enrollmentsToUpdate as $enrollment) {
            $enrollment->update(['program_level_id' => $degreeLevel->id]);
        }

        $this->command->info("✅ Assigned program levels to all enrollments");
    }
}
