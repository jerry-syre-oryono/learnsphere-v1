<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * StudentCourseResult Model
 *
 * Stores final grades and GPA information for each student-course combination.
 * This is the audit trail for grading decisions.
 */
class StudentCourseResult extends Model
{
    use HasFactory;

    protected $table = 'student_course_results';

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'final_mark',          // Percentage mark (0-100)
        'letter_grade',        // e.g., 'A', 'B+', 'C'
        'grade_point',         // Grade point value (0.0 - 5.0)
        'grade_points_earned', // grade_point × credit_units
        'credit_units',        // Default 3.0
        'semester',            // e.g., '2024-2025-1' or 'Spring 2025'
        'is_retake',           // Whether this is a retaken course
        'was_capped',          // Whether retake cap was applied
        'original_grade',      // Original grade before capping (for audit)
        'capped_grade',        // Capped grade after retake policy
        'calculated_at',       // When grades were calculated
    ];

    protected $casts = [
        'final_mark' => 'float',
        'grade_point' => 'float',
        'grade_points_earned' => 'float',
        'credit_units' => 'float',
        'is_retake' => 'boolean',
        'was_capped' => 'boolean',
        'calculated_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->enrollment->user();
    }
}
