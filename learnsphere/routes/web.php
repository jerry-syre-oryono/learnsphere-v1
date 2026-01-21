<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\UserManagementController;
use App\Livewire\Student\Dashboard as StudentDashboard; // Alias for clarity
use App\Livewire\Student\CourseDisplay;
use App\Livewire\Student\LessonView;
use App\Livewire\Student\Profile;
use App\Livewire\Admin\Dashboard as AdminDashboard; // Alias for clarity
use App\Livewire\Quiz\TakeQuiz;
use App\Livewire\Quiz\QuizResult;


Route::get('/', fn() => view('welcome'))->name('home');

    Route::get('dashboard', StudentDashboard::class)
    ->middleware(['auth', 'verified', 'approved'])
    ->name('dashboard');

    Route::get('profile', Profile::class)
    ->middleware(['auth', 'verified', 'approved', 'role:student'])
    ->name('student.profile');

    Volt::route('results', 'student.results')
    ->middleware(['auth', 'verified', 'approved', 'role:student'])
    ->name('student.results');

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
    Route::get('admin/courses/{course}/edit', App\Http\Controllers\Admin\CourseEditController::class)->name('admin.courses.edit')->middleware('role:admin|instructor');
    Route::put('admin/courses/{course}', [App\Http\Controllers\Admin\CourseController::class, 'update'])->name('admin.courses.update')->middleware('role:admin|instructor');

    Volt::route('admin/users', 'admin.usermanagement')->name('admin.users')->middleware('role:admin');
    Route::get('admin/user-management', [UserManagementController::class, 'index'])->name('admin.user-management.index')->middleware('role:admin|instructor');
    Route::get('admin/user-management/{user}', [UserManagementController::class, 'show'])->name('admin.user-management.show')->middleware('role:admin|instructor');
    Route::delete('admin/user-management/{user}', [UserManagementController::class, 'destroy'])->name('admin.user-management.destroy')->middleware('role:admin');
    Route::get('admin/gradebook', [GradebookController::class, 'index'])->name('admin.gradebook')->middleware('role:admin|instructor');
    Route::get('/admin/courses/{course}/grade-report', function (App\Models\Course $course) {
        return view('admin.grade-report', ['course' => $course]);
    })->name('admin.grade-report')->middleware('role:admin|instructor');

    // New Grade Reports Landing Page
    Route::get('/admin/grade-reports', App\Livewire\Admin\CourseListForGrades::class)
        ->name('admin.grade-reports-landing')
        ->middleware('role:admin|instructor');

    // Other admin routes like managing courses, quizzes etc. would go here

    // Instructor/Admin specific routes for Gradebook (from previous steps)
    Route::get('courses/{course}/gradebook', [GradebookController::class, 'index'])->name('gradebook.index')->middleware('role:admin|instructor');
    Route::get('courses/{course}/gradebook/export', [GradebookController::class, 'export'])->name('gradebook.export')->middleware('role:admin|instructor');
    Route::post('admin/courses/{course}/process-grades', [GradebookController::class, 'processGrades'])->name('admin.courses.process-grades')->middleware('role:admin|instructor');

    // Module API Routes
    Route::prefix('api')->middleware('role:admin|instructor')->group(function () {
        Route::post('courses/{course}/modules', [App\Http\Controllers\ModuleController::class, 'store'])->name('api.modules.store');
        Route::put('modules/{module}', [App\Http\Controllers\ModuleController::class, 'update'])->name('api.modules.update');
        Route::post('courses/{course}/modules/reorder', [App\Http\Controllers\ModuleController::class, 'reorder'])->name('api.modules.reorder');
        Route::delete('modules/{module}', [App\Http\Controllers\ModuleController::class, 'destroy'])->name('api.modules.destroy');
        Route::post('modules/{module}/duplicate', [App\Http\Controllers\ModuleController::class, 'duplicate'])->name('api.modules.duplicate');

        // Lesson API Routes
        Route::post('modules/{module}/lessons', [App\Http\Controllers\LessonController::class, 'store'])->name('api.lessons.store');
        Route::put('lessons/{lesson}', [App\Http\Controllers\LessonController::class, 'update'])->name('api.lessons.update');
        Route::post('lessons/{lesson}/upload', [App\Http\Controllers\LessonController::class, 'uploadMaterial'])->name('api.lessons.upload');
        Route::delete('media/{media}', [App\Http\Controllers\LessonController::class, 'deleteMedia'])->name('api.media.destroy');
        Route::post('modules/{module}/lessons/reorder', [App\Http\Controllers\LessonController::class, 'reorder'])->name('api.lessons.reorder');
        Route::delete('lessons/{lesson}', [App\Http\Controllers\LessonController::class, 'destroy'])->name('api.lessons.destroy');

        // Assessment API Routes
        Route::post('lessons/{lesson}/assessments', [App\Http\Controllers\AssessmentController::class, 'store'])->name('api.assessments.store');
        Route::put('assessments/{assessment}', [App\Http\Controllers\AssessmentController::class, 'update'])->name('api.assessments.update');
        Route::get('quizzes/{quiz}/questions', [App\Http\Controllers\AssessmentController::class, 'getQuestions'])->name('api.questions.index');
        Route::post('quizzes/{quiz}/questions', [App\Http\Controllers\AssessmentController::class, 'addQuestion'])->name('api.questions.store');
        Route::post('quizzes/{quiz}/questions/sync', [App\Http\Controllers\AssessmentController::class, 'syncQuestions'])->name('api.questions.sync');
        Route::put('questions/{question}', [App\Http\Controllers\AssessmentController::class, 'updateQuestion'])->name('api.questions.update');
        Route::delete('questions/{question}', [App\Http\Controllers\AssessmentController::class, 'deleteQuestion'])->name('api.questions.destroy');
        Route::get('quizzes/{quiz}/stats', [App\Http\Controllers\AssessmentController::class, 'stats'])->name('api.assessments.stats');
        Route::post('responses/{response}/grade', [App\Http\Controllers\AssessmentController::class, 'gradeEssay'])->name('api.responses.grade');
    });

    // Student Assessment Routes
    Route::post('quizzes/{quiz}/start', [App\Http\Controllers\AssessmentController::class, 'startAttempt'])->name('quiz.start');
    Route::post('submissions/{submission}/submit', [App\Http\Controllers\AssessmentController::class, 'submit'])->name('submission.submit');
    Route::get('quizzes/{quiz}/result', [App\Http\Controllers\AssessmentController::class, 'result'])->name('quiz.myresult');

    // Secure Media Streaming
    Route::get('lessons/media/{media}/stream', [App\Http\Controllers\LessonController::class, 'streamMedia'])->name('lesson.media.stream');
    Route::get('lessons/{lesson}/attachment/stream', [App\Http\Controllers\LessonController::class, 'streamAttachment'])->name('lesson.attachment.stream');
    Route::get('assignments/{assignment}/stream', [App\Http\Controllers\AssignmentController::class, 'stream'])->name('assignment.stream');
    // Allow GET to the submit URL to gracefully redirect back to the course page
    Route::get('assignments/{assignment}/submit', function (App\Models\Assignment $assignment) {
        $course = $assignment->module->course;
        return redirect()->route('course.show', $course);
    })->name('assignment.submit.get');

    Route::post('assignments/{assignment}/submit', [App\Http\Controllers\AssignmentController::class, 'submit'])->name('assignment.submit');
    Route::get('submissions/{submission}/download', [App\Http\Controllers\SubmissionController::class, 'download'])->name('submission.download');
    Route::post('submissions/{submission}/grade', [App\Http\Controllers\SubmissionController::class, 'grade'])->name('submission.grade');

    // Grading Routes
    Route::get('student/grades', fn() => view('grades.student-report'))->name('student.grades');
    Route::get('api/student/grade-report', [App\Http\Controllers\GradeReportController::class, 'getStudentGradeReport'])->name('api.grades.report');
    Route::get('api/students/{student}/cgpa', [App\Http\Controllers\GradeReportController::class, 'getStudentCGPA'])->name('api.students.cgpa');
    Route::get('api/enrollments/{enrollment}/grades', [App\Http\Controllers\GradeReportController::class, 'getEnrollmentGrades'])->name('api.enrollments.grades');
    Route::get('api/academic-policies', [App\Http\Controllers\GradeReportController::class, 'getAcademicPolicies'])->name('api.academic-policies');

    // Admin Grade Processing Routes
    Route::middleware(['role:instructor|admin'])->group(function () {
        Route::post('api/admin/grades/process', [App\Http\Controllers\Admin\GradeProcessingController::class, 'processGrade'])->name('api.admin.grades.process');
        Route::post('api/admin/grades/bulk-process', [App\Http\Controllers\Admin\GradeProcessingController::class, 'bulkProcessGrades'])->name('api.admin.grades.bulk-process');
        Route::get('api/admin/courses/{course}/results', [App\Http\Controllers\Admin\GradeProcessingController::class, 'getCourseResults'])->name('api.admin.courses.results');
        Route::put('api/admin/results/{result}', [App\Http\Controllers\Admin\GradeProcessingController::class, 'updateGrade'])->name('api.admin.results.update');
    });
});

require __DIR__ . '/auth.php';
