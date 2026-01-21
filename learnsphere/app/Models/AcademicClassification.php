<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * AcademicClassification Model
 *
 * Stores classification levels for different program types.
 * Examples:
 * - Diploma: Distinction, Credit, Pass, Fail
 * - Degree: First Class, Second Class Upper, Second Class Lower, Pass, Fail
 */
class AcademicClassification extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_level_id',
        'min_cgpa',            // Minimum CGPA for this classification
        'max_cgpa',            // Maximum CGPA for this classification
        'classification',      // Classification name (e.g., 'Distinction', 'First Class')
        'class',               // Degree class (e.g., 'First Class Honours')
        'description',
        'order',               // Display order
    ];

    protected $casts = [
        'min_cgpa' => 'float',
        'max_cgpa' => 'float',
    ];

    public function programLevel()
    {
        return $this->belongsTo(ProgramLevel::class);
    }
}
