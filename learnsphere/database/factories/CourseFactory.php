<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
use App\Models\User;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;
        return [
            'instructor_id' => User::factory(), // Or get/create a user with 'instructor' role
            'title' => $title,
            'description' => $this->faker->paragraph,
            'slug' => Str::slug($title),
            'published' => true,
        ];
    }
}
