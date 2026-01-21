<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ProgramLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'program_level_id' => ProgramLevel::factory(),
        ];
    }
}
