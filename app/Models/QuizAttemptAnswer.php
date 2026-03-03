<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttemptAnswer extends Model
{
    protected $fillable = ['quiz_attempt_id', 'question_id', 'question_option_id', 'is_correct', 'points_earned'];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function quizAttempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function questionOption()
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
