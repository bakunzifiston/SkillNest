<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Contracts\View\View;

class CourseProgressController extends Controller
{
    /**
     * List all courses for student progress (click a course to see enrolled students).
     */
    public function index(): View
    {
        $courses = Course::withCount('enrollments')
            ->with('category')
            ->orderBy('title')
            ->get();

        return view('admin.course-progress.index', compact('courses'));
    }

    /**
     * Show students enrolled in this course with completion rate, viewed %, completed at, started at.
     */
    public function show(Course $course): View
    {
        $course->load(['chapters.lessons']);
        $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
        $lessonIds = $course->chapters->flatMap->lessons->pluck('id');

        $enrollments = $course->enrollments()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $userIds = $enrollments->pluck('user_id');
        $completionsByUser = \App\Models\LessonCompletion::query()
            ->whereIn('user_id', $userIds)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->groupBy('user_id');

        $rows = $enrollments->map(function ($enrollment) use ($totalLessons, $lessonIds, $completionsByUser) {
            $userCompletions = $completionsByUser->get($enrollment->user_id, collect());
            $completed = $userCompletions->count();
            $completedAt = $userCompletions->max('completed_at');
            $percent = $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0;
            return (object) [
                'enrollment' => $enrollment,
                'user' => $enrollment->user,
                'started_at' => $enrollment->created_at,
                'completed_count' => $completed,
                'total_lessons' => $totalLessons,
                'completion_percent' => $percent,
                'viewed_percent' => $percent, // same as completion; can later add lesson_views table for true "viewed"
                'completed_at' => $completedAt,
            ];
        });

        return view('admin.course-progress.show', compact('course', 'totalLessons', 'rows'));
    }
}
