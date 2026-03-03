<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'passing_grade',
        'time_limit_minutes', 'sort_order', 'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
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
