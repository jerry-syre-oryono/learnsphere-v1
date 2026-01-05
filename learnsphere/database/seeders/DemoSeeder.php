<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find the demo student user
        $student = User::where('email', 'student@learnsphere.com')->first();
        
        // Find the first available course
        $course = Course::first();

        // Enroll the student in the course, if both exist
        if ($student && $course) {
            Enrollment::firstOrCreate([
                'user_id' => $student->id,
                'course_id' => $course->id,
            ]);
        }
    }
}
