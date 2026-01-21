<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * 
 * AcademicPolicy Model
 *
 * Stores NCHE-aligned academic policies and regulations.
 * Used for displaying policy information in the UI and enforcing rules.
 */
class AcademicPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_code',         // e.g., 'PASS_MARK', 'RETAKE_CAP', 'GRAD_CGPA'
        'policy_name',         // e.g., 'Pass Mark Requirement'
        'description',         // Policy text
        'value',               // Policy value (e.g., '50' for pass mark)
        'policy_type',         // e.g., 'regulation', 'guideline', 'requirement'
        'is_active',
        'order',               // Display order
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
