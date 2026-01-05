<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Module;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\Question;

class CourseContentSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Instructor
        $instructor = \App\Models\User::first();
        if (!$instructor) {
            $instructor = \App\Models\User::factory()->create();
        }

        // 2. Clean up existing course if it exists (to prevent duplicates and ensure fresh data)
        $existingCourse = Course::where('slug', 'laravel-mastery-beginner-to-pro')->first();
        if ($existingCourse) {
            $existingCourse->delete();
        }

        // 3. Create Course
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Laravel Mastery: From Beginner to Pro',
            'slug' => 'laravel-mastery-beginner-to-pro',
            'description' => 'Master the PHP framework for web artisans. Build robust applications with ease. cover all aspects from routing, controllers, views to advanced topics like queues and events.',
            'thumbnail' => 'https://laravel.com/img/logomark.min.svg',
            'published' => true,
        ]);

        // 4. Create Module
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'Getting Started with Laravel',
            'order' => 1,
        ]);

        // 5. Create Lessons
        $lesson = Lesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Installation and Setup',
            'content' => 'In this lesson, we will learn how to install Laravel via Composer.',
            'video_url' => 'https://www.youtube.com/watch?v=imTpU07nHO4',
            'order' => 1,
        ]);

        $lesson2 = Lesson::create([
            'course_id' => $course->id,
            'module_id' => $module->id,
            'title' => 'Routing Basics',
            'content' => 'Understanding how web.php works is crucial for defining your application routes.',
            'order' => 2,
        ]);

        // Add an Assignment
        \App\Models\Assignment::create([
            'module_id' => $module->id,
            'title' => 'Install Laravel Challenge',
            'description' => 'Submit a screenshot of your running Laravel welcome page.',
            'max_score' => 50,
            'due_date' => now()->addWeek(),
        ]);

        // 6. Create Quiz
        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Laravel Basics Quiz',
            'randomize' => true,
        ]);

        // 7. Add Questions
        Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'What is Laravel?',
            'type' => Question::TYPE_MCQ,
            'options' => [
                'A' => 'A PHP Framework',
                'B' => 'A JavaScript Library',
                'C' => 'A Database Management System',
                'D' => 'A Operating System'
            ],
            'correct_answer' => 'A',
            'points' => 10,
        ]);

        Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'Which command is used to start the development server?',
            'type' => Question::TYPE_MCQ,
            'options' => [
                'A' => 'php artisan run',
                'B' => 'npm run dev',
                'C' => 'php artisan serve',
                'D' => 'server:start'
            ],
            'correct_answer' => 'C',
            'points' => 10,
        ]);

        $this->command->info('Real course content seeded successfully!');
    }
}
