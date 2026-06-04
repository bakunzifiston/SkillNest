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

    /**
     * Public URL for uploaded lesson files (video/PDF), without relying on storage:link.
     */
    public function fileUrl(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }

        return url('lesson-file/' . ltrim($this->file_path, '/'));
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
