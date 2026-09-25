<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'courses_count'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function publishedCourses()
    {
        return $this->hasMany(Course::class)->published();
    }
}
