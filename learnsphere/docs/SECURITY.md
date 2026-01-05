# LearnSphere Security Documentation

## Course Content Security

This document outlines the security measures implemented for file uploads, access control, and data protection in the LearnSphere platform.

---

## 1. File Upload Security

### Path Traversal Prevention
All uploaded files are stored with sanitized filenames:
```php
// In LessonService::uploadMedia()
$safeFilename = Str::slug($originalName) . '-' . Str::random(8) . '.' . $extension;
```
- Original filenames are slugified to remove special characters
- A random 8-character string is appended for uniqueness
- Only the original extension is preserved
- Files are stored in structured paths: `courses/{course_id}/modules/{module_id}/lessons/{lesson_id}/`

### MIME Type Validation
Only allowed file types can be uploaded:
```php
public const ALLOWED_MIME_TYPES = [
    'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
    'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'video' => ['video/mp4', 'video/webm', 'video/quicktime'],
];
```
Files are validated **before** storage occurs. Invalid MIME types result in an `InvalidArgumentException`.

### Private Storage
All course uploads are stored on the `private` disk by default:
- Files are **NOT** publicly accessible
- Access requires authentication via `LessonController@streamMedia`
- The controller checks enrollment/ownership before streaming files

---

## 2. Access Control

### Enrollment-Based Access
Students can only access course content if they are enrolled:
```php
// In LessonController::streamMedia()
$isEnrolled = $user->enrolledCourses()->where('course_id', $course->id)->exists();
$isInstructor = $course->instructor_id === $user->id;
$isAdmin = $user->hasRole('admin');

if (!$isEnrolled && !$isInstructor && !$isAdmin) {
    abort(403, 'Access denied');
}
```

### Course Policy
The `CoursePolicy` class controls who can:
- **View**: Anyone (for published courses)
- **Create**: Admins and Instructors
- **Update**: The course owner (instructor) or Admin
- **Delete**: The course owner or Admin

### API Route Protection
All API routes for course management require the `role:admin|instructor` middleware:
```php
Route::prefix('api')->middleware('role:admin|instructor')->group(function () {
    // Module, Lesson, Assessment management routes
});
```

---

## 3. Quiz/Assessment Security

### Attempt Limits
Users cannot exceed the configured maximum attempts:
```php
$attemptCount = Submission::where('user_id', $user->id)
    ->where('quiz_id', $quiz->id)
    ->count();

if ($attemptCount >= $quiz->max_attempts) {
    throw new \Exception("Maximum attempts reached for this quiz.");
}
```

### Question Randomization
Quizzes can be configured to:
- Randomize question order
- Select a subset of questions per attempt (question pools)

### Time Limits
Quizzes support time limits that should be enforced client-side and validated server-side.

### Answer Protection
- Correct answers are not sent to the client until after submission (if `show_answers_after_submit` is enabled)
- Essay questions require manual grading

---

## 4. User Role Integration

### Existing Roles
LearnSphere uses Spatie Laravel Permission with the following roles:
- **Admin**: Full platform access
- **Instructor**: Can create and manage their own courses
- **Student**: Can enroll and access published courses

### Instructor Restrictions
Instructors can only edit courses they created:
```php
// In CoursePolicy::update()
return $user->hasRole('admin') || $course->instructor_id === $user->id;
```

---

## 5. Database Security

### Soft Relationships
Cascade deletes are configured to remove dependent data:
- Deleting a Course removes all Modules, Lessons, and Media
- Deleting a Quiz removes all Questions and Submissions

### Answer Storage
Quiz answers are stored as JSON in the `answers` column with proper casting:
```php
protected $casts = [
    'answers' => 'array',
];
```

---

## 6. Best Practices Checklist

- [x] Path traversal prevention via filename sanitization
- [x] MIME type validation before storage
- [x] Private disk for sensitive uploads
- [x] Enrollment-based access control
- [x] Role-based authorization via Policy classes
- [x] Quiz attempt limiting
- [x] Question randomization for academic integrity
- [x] Separate storage paths per course/module/lesson

---

## 7. Recommended Enhancements

1. **File Virus Scanning**: Integrate ClamAV or a cloud scanning service
2. **Rate Limiting**: Add rate limits to upload endpoints
3. **Audit Logging**: Log all file uploads and access attempts
4. **Signed URLs**: For S3 storage, use signed URLs with expiration
5. **Content Disposition**: Force downloads for certain file types to prevent XSS

---

## Configuration Files

- **Filesystem**: `config/filesystems.php` - Defines the `private` disk
- **CORS**: Ensure API routes have proper CORS settings for frontend uploads
- **PHP Settings**: Increase `upload_max_filesize` and `post_max_size` for large files
