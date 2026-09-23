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

    /**
     * Extract a YouTube video id from source_url when this is a YouTube lesson.
     */
    public function youtubeVideoId(): ?string
    {
        if ($this->type !== self::TYPE_YOUTUBE || empty($this->source_url)) {
            return null;
        }

        $url = trim($this->source_url);

        if (preg_match('/(?:youtube\.com\/(?:watch\?(?:[^#]*&)?v=|embed\/|shorts\/|live\/|v\/)|youtu\.be\/|youtube-nocookie\.com\/embed\/)([a-zA-Z0-9_-]{11})/i', $url, $match)) {
            return $match[1];
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        return null;
    }

    public function youtubeEmbedUrl(): ?string
    {
        $id = $this->youtubeVideoId();
        if (! $id) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$id.'?rel=0&modestbranding=1&playsinline=1&enablejsapi=1';
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
