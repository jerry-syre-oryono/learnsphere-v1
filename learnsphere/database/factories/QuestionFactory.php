<?php

namespace Database\Factories;

use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'content' => $this->faker->sentence . '?',
            'type' => 'mcq',
            'options' => [
                ['text' => $this->faker->sentence, 'is_correct' => true, 'points' => 5],
                ['text' => $this->faker->sentence, 'is_correct' => false, 'points' => 0],
                ['text' => $this->faker->sentence, 'is_correct' => false, 'points' => 0],
            ],
        ];
    }
}
