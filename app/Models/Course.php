<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['category_id', 'instructor_id', 'title', 'slug', 'description', 'image', 'price', 'duration', 'level', 'students_count'];

    /**
     * Full URL for the course banner image (for use in img src).
     * Served via route so it works even when storage:link is not run.
     */
    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->image)) {
            return null;
        }
        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }
        return url('course-image/' . ltrim($this->image, '/'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderBy('sort_order');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class)->orderBy('sort_order');
    }

    public function liveSessions()
    {
        return $this->hasMany(LiveSession::class)->orderBy('scheduled_at');
    }

    public function bundles()
    {
        return $this->belongsToMany(Bundle::class, 'bundle_courses')
            ->withPivot('order');
    }

    public function totalLessonsCount(): int
    {
        return $this->chapters->sum(fn ($ch) => $ch->lessons->count());
    }
}
