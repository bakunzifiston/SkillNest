<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasTranslations;

    /** Fields that can be translated (see HasTranslations). */
    protected array $translatable = ['name'];

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
