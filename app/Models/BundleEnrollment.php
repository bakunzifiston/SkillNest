<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tracks when a user enrolled in a bundle and their progress.
 *
 * Progress tracking:
 * - completed_courses: number of courses in the bundle where the user completed all lessons
 * - total_courses: number of courses in the bundle
 * - bundle_completion_percentage: (completed_courses / total_courses) * 100
 * - completed_at: when the user finished the last course (all lessons in every course)
 *
 * Progress is updated by refreshProgress(), which is called:
 * - when the user views the bundle page (if enrolled)
 * - when the user enrolls in the bundle
 * - when the user marks a lesson complete (CourseController::completeLesson)
 */
class BundleEnrollment extends Model
{
    protected $fillable = [
        'user_id', 'bundle_id', 'enrolled_at', 'expires_at',
        'completed_courses', 'total_courses', 'bundle_completion_percentage', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bundle()
    {
        return $this->belongsTo(Bundle::class);
    }

    /**
     * Recompute and update progress from course enrollments and lesson completions.
     */
    public function refreshProgress(): void
    {
        $bundle = $this->bundle;
        $bundle->load('courses.chapters.lessons');
        $totalCourses = $bundle->courses->count();
        if ($totalCourses === 0) {
            $this->update([
                'total_courses' => 0,
                'completed_courses' => 0,
                'bundle_completion_percentage' => 0,
                'completed_at' => null,
            ]);
            return;
        }

        $completed = 0;
        foreach ($bundle->courses as $course) {
            $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
            if ($totalLessons === 0) {
                $completed++;
                continue;
            }
            $done = $this->user->completedLessonsCountForCourse($course);
            if ($done >= $totalLessons) {
                $completed++;
            }
        }

        $pct = $totalCourses > 0 ? round(($completed / $totalCourses) * 100, 2) : 0;
        $completedAt = ($completed >= $totalCourses) ? now() : null;

        $this->update([
            'total_courses' => $totalCourses,
            'completed_courses' => $completed,
            'bundle_completion_percentage' => $pct,
            'completed_at' => $this->completed_at ?? $completedAt,
        ]);
    }
}
