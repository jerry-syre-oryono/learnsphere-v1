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
}
