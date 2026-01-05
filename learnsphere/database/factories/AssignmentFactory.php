<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => \App\Models\Module::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'max_score' => 100,
            'due_date' => $this->faker->dateTimeBetween('now', '+1 month'),
        ];
    }
}
