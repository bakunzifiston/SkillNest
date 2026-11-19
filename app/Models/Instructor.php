<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    use HasTranslations;

    /** Fields that can be translated (see HasTranslations). */
    protected array $translatable = ['name', 'bio'];

    protected $fillable = ['name', 'email', 'bio', 'photo'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
