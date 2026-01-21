<?php

namespace Database\Factories;

use App\Models\ProgramLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramLevelFactory extends Factory
{
    protected $model = ProgramLevel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->unique()->regexify('[A-Z]{3}')),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'require_cgpa_for_graduation' => true,
        ];
    }
}
