<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    public const STATUS_STARTED = 'started';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PENDING_REVIEW = 'pending_review';

    protected $guarded = [];

    protected $casts = [
        'answers' => 'array',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function submittable()
    {
        return $this->morphTo();
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionResponse::class);
    }

    public function isPassed(): bool
    {
        return $this->percentage >= $this->quiz->passing_score;
    }
}
