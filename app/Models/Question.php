<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    public const TYPE_MCQ = 'mcq';
    public const TYPE_TRUE_FALSE = 'true_false';

    protected $fillable = ['quiz_id', 'type', 'question_text', 'points', 'sort_order'];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function isMcq(): bool
    {
        return $this->type === self::TYPE_MCQ;
    }

    public function isTrueFalse(): bool
    {
        return $this->type === self::TYPE_TRUE_FALSE;
    }
}
