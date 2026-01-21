<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ProgramLevel Model
 *
 * Represents different academic program levels:
 * - Diploma
 * - Degree (Undergraduate/Postgraduate)
 * - Certificate
 */
class ProgramLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',          // e.g., 'Diploma', 'Degree', 'Certificate'
        'code',          // e.g., 'DIPL', 'DEG', 'CERT'
        'description',
        'is_active',
        'require_cgpa_for_graduation',  // Whether CGPA classification is required
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'require_cgpa_for_graduation' => 'boolean',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function gradingRules()
    {
        return $this->hasMany(GradingRule::class);
    }

    public function academicClassifications()
    {
        return $this->hasMany(AcademicClassification::class);
    }
}
