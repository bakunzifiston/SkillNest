<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'category_id',
        'instructor_id',
        'title',
        'slug',
        'description',
        'image',
        'price',
        'duration',
        'level',
        'status',
        'students_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (Course $course) {
            if (blank($course->status)) {
                $course->status = self::STATUS_DRAFT;
            }
        });
    }

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

        return url('course-image/'.ltrim($this->image, '/'));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
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
