<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasTranslations;

    /** Fields that can be translated (see HasTranslations). */
    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'course_id', 'chapter_id', 'lesson_id', 'title', 'description', 'passing_grade',
        'time_limit_minutes', 'max_attempts', 'allow_retake_after_pass', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'allow_retake_after_pass' => 'boolean',
        'max_attempts' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    /**
     * Where a student stands with this quiz: attempts used, an unfinished attempt to resume,
     * and whether they may start another one (maximum attempts / retake after passing).
     * Attempts whose time ran out are closed first, so they count as used.
     *
     * @return array{used: int, max: int|null, unfinished: QuizAttempt|null, passed: bool, canStart: bool, reason: string|null, last: QuizAttempt|null}
     */
    public function attemptStateFor(User $user): array
    {
        $mine = fn () => $this->attempts()->where('user_id', $user->id);

        $mine()->whereNull('submitted_at')->get()->each(function (QuizAttempt $attempt) {
            $attempt->setRelation('quiz', $this);
            if ($attempt->isExpired()) {
                $attempt->closeAsTimedOut();
            }
        });

        $used = $mine()->whereNotNull('submitted_at')->count();
        $passed = $mine()->whereNotNull('submitted_at')->where('passed', true)->exists();
        $unfinished = $mine()->whereNull('submitted_at')->latest('id')->first();
        $last = $mine()->whereNotNull('submitted_at')->latest('id')->first();

        $reason = null;
        if ($unfinished === null) {
            if (! $this->allow_retake_after_pass && $passed) {
                $reason = 'passed';
            } elseif ($this->max_attempts !== null && $used >= $this->max_attempts) {
                $reason = 'limit';
            }
        }

        return [
            'used' => $used,
            'max' => $this->max_attempts,
            'unfinished' => $unfinished,
            'passed' => $passed,
            'canStart' => $reason === null,
            'reason' => $reason,
            'last' => $last,
        ];
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }
}
