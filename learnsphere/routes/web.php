<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\MediaController;
use App\Livewire\Student\Dashboard as StudentDashboard; // Alias for clarity
use App\Livewire\Student\CourseDisplay;
use App\Livewire\Student\LessonView;
use App\Livewire\Admin\Dashboard as AdminDashboard; // Alias for clarity
use App\Livewire\Quiz\TakeQuiz;
use App\Livewire\Quiz\QuizResult;


Route::get('/', fn() => view('welcome'))->name('home');

Route::get('dashboard', StudentDashboard::class)
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

// Authenticated user routes
Route::middleware(['auth', 'approved'])->group(function () {
    // Settings Routes
    Route::redirect('settings', 'settings/profile');
    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Course and Lesson Display Routes
    Route::get('/courses/{course}', CourseDisplay::class)->name('course.show');
    Route::get('/lessons/{lesson}', LessonView::class)->name('lesson.show');

    // Quiz Routes
    Route::get('/quizzes/{quiz}/take', TakeQuiz::class)->name('quiz.take');
    Route::get('/submissions/{submission}/result', QuizResult::class)->name('quiz.result');

    // Media Upload and Streaming Routes (from previous steps)
    Route::post('lessons/{lesson}/upload', [MediaController::class, 'upload'])->name('media.upload');
    Route::get('media/{media}/stream', [MediaController::class, 'stream'])->name('media.stream');
    Route::get('media/{media}/serve', [MediaController::class, 'serve'])->name('media.stream.serve');

    // Admin-specific routes (from previous steps)
    Volt::route('admin/dashboard', 'admin.dashboard')->name('admin.dashboard')->middleware('role:admin|instructor');

    // Explicit Controller Routes for complex creation logic
    Route::get('admin/courses/create', [App\Http\Controllers\Admin\CourseController::class, 'create'])->name('admin.courses.create')->middleware('role:admin|instructor');
    Route::post('admin/courses', [App\Http\Controllers\Admin\CourseController::class, 'store'])->name('admin.courses.store')->middleware('role:admin|instructor');

    Volt::route('admin/users', 'admin.usermanagement')->name('admin.users')->middleware('role:admin');

    // Other admin routes like managing courses, quizzes etc. would go here

    // Instructor/Admin specific routes for Gradebook (from previous steps)
    Route::get('courses/{course}/gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('courses/{course}/gradebook/export', [GradebookController::class, 'export'])->name('gradebook.export');
});

require __DIR__ . '/auth.php';
