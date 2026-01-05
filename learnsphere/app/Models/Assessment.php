<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Assessment extends Model
{
    use HasFactory;

    public const TYPE_QUIZ = 'quiz';
    public const TYPE_EXAM = 'exam';

    protected $guarded = [];

    protected $casts = [
        'time_limit' => 'integer',
        'max_attempts' => 'integer',
        'randomize_questions' => 'boolean',
        'questions_per_attempt' => 'integer',
        'passing_score' => 'decimal:2',
        'weight' => 'decimal:2',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'is_published' => 'boolean',
        'auto_grade' => 'boolean',
        'show_answers_after_submit' => 'boolean',
    ];

    /**
     * Get the parent assessable model (Module or Lesson).
     */
    public function assessable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the questions for this assessment.
     * Note: Questions are linked via Quiz model for backward compatibility.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'quiz_id')->orderBy('order');
    }

    /**
     * Check if the assessment is currently available.
     */
    public function isAvailable(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        $now = now();

        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }

        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a user can attempt this assessment.
     */
    public function canAttempt(User $user): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $attemptCount = Submission::where('user_id', $user->id)
            ->where('quiz_id', $this->id)
            ->count();

        return $attemptCount < $this->max_attempts;
    }
}
