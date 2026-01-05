<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class LmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 2 courses, each with 2 modules. Each module has 3 lessons.
        Course::factory(2)
            ->has(
                Module::factory(2)
                    ->has(
                        Lesson::factory(3)
                            ->has(
                                Quiz::factory()
                                    ->has(Question::factory(5))
                            )
                    )
            )
            ->create();
    }
}
