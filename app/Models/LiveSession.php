<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveSession extends Model
{
    protected $fillable = [
        'course_id', 'title', 'description', 'scheduled_at', 'duration_minutes',
        'meeting_url', 'meeting_password',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function invitedAttendees()
    {
        return $this->belongsToMany(User::class, 'live_session_user')->withTimestamps();
    }

    public function isUpcoming(): bool
    {
        return $this->scheduled_at->isFuture();
    }

    public function isPast(): bool
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes)->isPast();
    }
}
