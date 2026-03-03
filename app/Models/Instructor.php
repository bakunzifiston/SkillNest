<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = ['name', 'email', 'bio', 'photo'];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
