<?php

namespace Database\Factories;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assessable_id' => Lesson::factory(),
            'assessable_type' => Lesson::class,
            'title' => $this->faker->sentence(),
            'type' => 'quiz',
            'weight' => $this->faker->numberBetween(10, 50),
            'max_attempts' => 1,
            'passing_score' => 70,
        ];
    }
}