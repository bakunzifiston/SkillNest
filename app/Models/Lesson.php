<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    public const TYPE_TEXT = 'text';
    public const TYPE_YOUTUBE = 'youtube';
    public const TYPE_PDF = 'pdf';
    public const TYPE_VIDEO = 'video';

    protected $fillable = ['chapter_id', 'title', 'type', 'content', 'source_url', 'file_path', 'sort_order'];

    public static function types(): array
    {
        return [
            self::TYPE_TEXT => 'Text content',
            self::TYPE_YOUTUBE => 'YouTube link',
            self::TYPE_PDF => 'PDF file',
            self::TYPE_VIDEO => 'Uploaded video',
        ];
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }
}
