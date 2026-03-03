<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['name', 'logo', 'sort_order'];

    public function getLogoUrlAttribute(): string
    {
        if (empty($this->logo)) {
            return '';
        }
        if (str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }
        return url('course-image/' . ltrim($this->logo, '/'));
    }
}
