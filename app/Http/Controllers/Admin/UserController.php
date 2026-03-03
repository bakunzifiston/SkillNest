<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * List all users (super admin): name, email, created, enrollments, last sign in.
     */
    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('enrollments')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->search;
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show a user's progress: all courses on the platform and their progress per course.
     */
    public function show(User $user): View
    {
        $courses = Course::with(['chapters.lessons', 'category'])
            ->orderBy('title')
            ->get();

        $enrollmentCourseIds = $user->enrollments()->pluck('course_id');
        $completedByLesson = $user->lessonCompletions()->pluck('lesson_id');

        $courseProgress = $courses->map(function (Course $course) use ($user, $enrollmentCourseIds, $completedByLesson) {
            $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
            $lessonIds = $course->chapters->flatMap->lessons->pluck('id');
            $completed = $completedByLesson->intersect($lessonIds)->count();
            return (object) [
                'course' => $course,
                'enrolled' => $enrollmentCourseIds->contains($course->id),
                'total_lessons' => $totalLessons,
                'completed' => $completed,
                'percent' => $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0,
            ];
        });

        return view('admin.users.show', compact('user', 'courseProgress'));
    }
}
