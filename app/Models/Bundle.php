<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = ['title', 'slug', 'description', 'thumbnail', 'status'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'bundle_courses')
            ->withPivot('order')
            ->orderBy('bundle_courses.order');
    }

    public function bundleEnrollments()
    {
        return $this->hasMany(BundleEnrollment::class);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }
        if (str_starts_with($this->thumbnail, 'http')) {
            return $this->thumbnail;
        }
        return url('course-image/' . ltrim($this->thumbnail, '/')); // same route serves courses/ and bundles/
    }

    public function totalCoursesCount(): int
    {
        return $this->courses()->count();
    }
}
