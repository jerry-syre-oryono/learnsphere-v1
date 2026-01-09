<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['instructor_id', 'title', 'description', 'slug', 'thumbnail', 'published', 'enrollment_code'];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'enrollments');
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order');
    }

    public function assignments(): HasManyThrough
    {
        return $this->hasManyThrough(Assignment::class, Module::class);
    }

    public function assessments()
    {
        $moduleIds = $this->modules()->pluck('id');
        $lessonIds = $this->lessons()->pluck('id');

        $moduleAssessments = Assessment::where('assessable_type', Module::class)
                                       ->whereIn('assessable_id', $moduleIds)
                                       ->get();

        $lessonAssessments = Assessment::where('assessable_type', Lesson::class)
                                       ->whereIn('assessable_id', $lessonIds)
                                       ->get();

        return $moduleAssessments->merge($lessonAssessments);
    }

    public function getAssessableItemsAttribute()
    {
        $assignments = $this->assignments;
        $assessments = $this->assessments();

        return $assignments->toBase()->merge($assessments);
    }

    public function getTotalWeightAttribute()
    {
        return $this->getAssessableItemsAttribute()->sum('weight');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * Get the course code derived from the course title.
     *
     * This method generates a course code by taking the first letter of each
     * significant word in the title (excluding common words like "in", "of", "the", etc.).
     *
     * Examples:
     * - "Diploma in VFX" → "DVFX"
     * - "Certificate in Web Development" → "CWD"
     * - "Bachelor of Computer Science" → "BCS"
     *
     * @return string The course code in uppercase
     */
    public function getCourseCode(): string
    {
        // Common words to exclude from course code generation
        $excludeWords = ['in', 'of', 'the', 'and', 'for', 'with', 'on', 'at', 'to', 'a', 'an'];

        $words = explode(' ', $this->title);

        $codeParts = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (!empty($word) && !in_array(strtolower($word), $excludeWords)) {
                // If word is all uppercase (likely an acronym), use up to 3 characters
                if ($word === strtoupper($word) && strlen($word) <= 3) {
                    $codeParts[] = strtoupper($word);
                } else {
                    // Otherwise use first letter
                    $codeParts[] = strtoupper(substr($word, 0, 1));
                }
            }
        }

        // If no significant words found, use first 4 characters of title
        if (empty($codeParts)) {
            return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $this->title), 0, 4));
        }

        // Join and limit to 4 characters maximum
        $code = implode('', $codeParts);
        return substr($code, 0, 4);
    }
}
