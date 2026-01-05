<?php

namespace Tests\Feature;

use App\Livewire\Student\LessonView;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LessonViewTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Lesson $lessonWithQuiz;
    private Lesson $lessonWithoutQuiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->student = User::factory()->create()->assignRole('student');
        $course = Course::factory()->create();
        $module = Module::factory()->for($course)->create();

        $this->lessonWithQuiz = Lesson::factory()->for($module)->for($course)->has(Quiz::factory())->create();
        $this->lessonWithoutQuiz = Lesson::factory()->for($module)->for($course)->create();

        // Ensure student is enrolled in the course to view lessons
        Enrollment::create(['user_id' => $this->student->id, 'course_id' => $course->id]);

        $this->actingAs($this->student);
    }

    public function test_lesson_view_page_renders_content(): void
    {
        Livewire::test(LessonView::class, ['lesson' => $this->lessonWithoutQuiz])
            ->assertSee($this->lessonWithoutQuiz->title)
            ->assertSee('Back to Course'); // A simple assertion to confirm the page loaded
    }

    public function test_user_can_mark_lesson_as_complete(): void
    {
        Livewire::test(LessonView::class, ['lesson' => $this->lessonWithoutQuiz])
            ->assertSee('Mark as Complete')
            ->call('markAsComplete')
            ->assertSet('isCompleted', true)
            ->assertSee('Completed');

        $this->student->refresh(); // Refresh the student model to get updated relationships
        $this->assertTrue($this->student->completedLessons->contains($this->lessonWithoutQuiz->id));
    }

    public function test_lesson_with_quiz_shows_start_quiz_button(): void
    {
        Livewire::test(LessonView::class, ['lesson' => $this->lessonWithQuiz])
            ->assertSee('Start Quiz');
    }
}
