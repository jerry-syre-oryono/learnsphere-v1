<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'module_id', 'title', 'content', 'video_url', 'downloadable_file', 'order', 'content_type', 'attachment_path', 'attachment_name'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function quiz(): HasOne
    {
        return $this->hasOne(Quiz::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(LessonMedia::class)->orderBy('order');
    }

    public function assessments(): MorphMany
    {
        return $this->morphMany(Assessment::class, 'assessable');
    }

    /**
     * Get the structured storage path for this lesson's files.
     */
    public function getStoragePathAttribute(): string
    {
        return "courses/{$this->course_id}/modules/{$this->module_id}/lessons/{$this->id}";
    }
}
