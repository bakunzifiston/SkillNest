<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'started_at', 'submitted_at',
        'score', 'total_points', 'percentage', 'passed', 'timed_out', 'draft_answers',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'passed' => 'boolean',
        'timed_out' => 'boolean',
        'draft_answers' => 'array',
    ];

    /** Extra seconds after the deadline that a submission is still accepted (slow connections, the auto-submit). */
    public const GRACE_SECONDS = 20;

    /** When the time limit runs out, or null if the quiz has no time limit. */
    public function expiresAt(): ?\Illuminate\Support\Carbon
    {
        $minutes = $this->quiz?->time_limit_minutes;

        return $minutes ? $this->started_at->copy()->addMinutes($minutes) : null;
    }

    public function isExpired(int $graceSeconds = 0): bool
    {
        $expires = $this->expiresAt();

        return $this->submitted_at === null && $expires !== null && now()->greaterThan($expires->copy()->addSeconds($graceSeconds));
    }

    /** Whole seconds left on the clock (never negative), or null without a time limit. */
    public function remainingSeconds(): ?int
    {
        $expires = $this->expiresAt();

        return $expires ? max(0, (int) now()->diffInSeconds($expires, false)) : null;
    }

    /**
     * Keep only answers that really belong to this quiz: question id => option id.
     *
     * @param  array<int|string, mixed>  $input
     * @return array<int, int>
     */
    public function sanitizeAnswers(array $input): array
    {
        $clean = [];

        foreach ($this->quiz->questions()->with('options:id,question_id')->get() as $question) {
            $optionId = $input[$question->id] ?? null;
            if (is_numeric($optionId) && $question->options->contains('id', (int) $optionId)) {
                $clean[$question->id] = (int) $optionId;
            }
        }

        return $clean;
    }

    /** Store the answers given so far (autosave), so they survive a closed tab and can be submitted when time runs out. */
    public function saveDraft(array $input): void
    {
        $this->update(['draft_answers' => $this->sanitizeAnswers($input) ?: null]);
    }

    /**
     * Grade the chosen answers and finish the attempt. $selected is question id => option id.
     * Questions without an answer score 0.
     *
     * @param  array<int|string, mixed>  $selected
     */
    public function submitAnswers(array $selected, bool $timedOut = false): void
    {
        if ($this->submitted_at !== null) {
            return;
        }

        $quiz = $this->quiz;
        $questions = $quiz->questions()->with('options')->get();
        $totalPoints = $questions->sum('points');
        $score = 0;

        foreach ($questions as $question) {
            $selectedId = $selected[$question->id] ?? null;
            $selectedOption = $selectedId ? $question->options->find($selectedId) : null;
            $isCorrect = (bool) ($selectedOption && $selectedOption->is_correct);
            $pointsEarned = $isCorrect ? $question->points : 0;
            $score += $pointsEarned;

            QuizAttemptAnswer::create([
                'quiz_attempt_id' => $this->id,
                'question_id' => $question->id,
                'question_option_id' => $selectedOption?->id,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        }

        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;

        $this->update([
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $percentage >= $quiz->passing_grade,
            'submitted_at' => $timedOut ? ($this->expiresAt() ?? now()) : now(),
            'timed_out' => $timedOut,
            'draft_answers' => null,
        ]);
    }

    /**
     * The time ran out: submit automatically what the student had saved by the deadline.
     * Anything that arrives later is ignored, so the deadline cannot be stretched.
     */
    public function closeAsTimedOut(): void
    {
        $this->submitAnswers($this->draft_answers ?? [], timedOut: true);
    }

    /** How many questions have an answer (for the result page). */
    public function answeredCount(): int
    {
        return $this->answers->whereNotNull('question_option_id')->count();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
