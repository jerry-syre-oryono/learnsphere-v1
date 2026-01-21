<?php

namespace Database\Factories;

use App\Models\StudentCourseResult;
use App\Models\Enrollment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentCourseResultFactory extends Factory
{
    protected $model = StudentCourseResult::class;

    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'course_id' => Course::factory(),
            'final_mark' => $this->faker->numberBetween(0, 100),
            'letter_grade' => $this->faker->randomElement(['A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F']),
            'grade_point' => $this->faker->randomFloat(1, 0, 5),
            'grade_points_earned' => $this->faker->randomFloat(2, 0, 20),
            'credit_units' => 3.0,
            'semester' => '2024-2025-1',
            'is_retake' => false,
            'was_capped' => false,
            'original_grade' => null,
            'capped_grade' => null,
        ];
    }
}
