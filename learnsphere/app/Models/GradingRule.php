<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * GradingRule Model
 *
 * Configurable grade boundaries per program level.
 * Supports NCHE-aligned grade scales.
 */
class GradingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_level_id',
        'min_percentage',      // Minimum percentage mark (e.g., 80)
        'max_percentage',      // Maximum percentage mark (e.g., 100)
        'letter_grade',        // Letter grade (e.g., 'A', 'B+', 'C')
        'grade_point',         // Grade point value (0.0 - 5.0)
        'description',
    ];

    protected $casts = [
        'min_percentage' => 'float',
        'max_percentage' => 'float',
        'grade_point' => 'float',
    ];

    public function programLevel()
    {
        return $this->belongsTo(ProgramLevel::class);
    }
}
