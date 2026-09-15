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
        $search = trim((string) $request->get('search', ''));

        $users = User::query()
            ->withCount('enrollments')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $totalUsers = User::count();
        $students = User::query()->where('is_admin', false)->count();
        $activeRecently = User::query()
            ->whereNotNull('last_login_at')
            ->where('last_login_at', '>=', now()->subDays(30))
            ->count();
        $withEnrollments = User::query()->whereHas('enrollments')->count();

        $kpis = [
            [
                'label' => 'Users',
                'value' => $totalUsers,
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Students',
                'value' => $students,
                'icon' => 'user',
                'tone' => 'success',
            ],
            [
                'label' => 'Active (30d)',
                'value' => $activeRecently,
                'icon' => 'pulse',
                'tone' => 'accent',
            ],
            [
                'label' => 'Enrolled',
                'value' => $withEnrollments,
                'icon' => 'enroll',
                'tone' => 'slate',
            ],
        ];

        return view('admin.users.index', compact('users', 'kpis', 'search'));
    }

    /**
     * Show a user's progress: all courses on the platform and their progress per course.
     */
    public function show(Request $request, User $user): View
    {
        $search = trim((string) $request->get('search', ''));
        $filter = $request->get('filter', 'all'); // all | enrolled

        $courses = Course::with(['chapters.lessons', 'category'])
            ->orderBy('title')
            ->get();

        $enrollmentCourseIds = $user->enrollments()->pluck('course_id');
        $completedByLesson = $user->lessonCompletions()->pluck('lesson_id');

        $courseProgress = $courses->map(function (Course $course) use ($enrollmentCourseIds, $completedByLesson) {
            $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
            $lessonIds = $course->chapters->flatMap->lessons->pluck('id');
            $completed = $completedByLesson->intersect($lessonIds)->count();
            $percent = $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0;
            $enrolled = $enrollmentCourseIds->contains($course->id);

            return (object) [
                'course' => $course,
                'enrolled' => $enrolled,
                'total_lessons' => $totalLessons,
                'completed' => $completed,
                'percent' => $percent,
                'status' => ! $enrolled
                    ? 'not_enrolled'
                    : ($percent >= 100 ? 'completed' : ($percent > 0 ? 'in_progress' : 'not_started')),
            ];
        });

        $enrolledRows = $courseProgress->where('enrolled', true);
        $completedCourses = $enrolledRows->where('status', 'completed')->count();
        $inProgressCourses = $enrolledRows->where('status', 'in_progress')->count();
        $avgProgress = $enrolledRows->count() > 0
            ? round((float) $enrolledRows->avg('percent'), 1)
            : 0;

        $kpis = [
            [
                'label' => 'Enrolled',
                'value' => $enrolledRows->count(),
                'icon' => 'book',
                'tone' => 'primary',
            ],
            [
                'label' => 'Completed',
                'value' => $completedCourses,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'In progress',
                'value' => $inProgressCourses,
                'icon' => 'pulse',
                'tone' => 'accent',
            ],
            [
                'label' => 'Avg. progress',
                'value' => $avgProgress,
                'suffix' => '%',
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        $rows = $courseProgress;
        if ($filter === 'enrolled') {
            $rows = $rows->where('enrolled', true)->values();
        }
        if ($search !== '') {
            $rows = $rows->filter(function ($row) use ($search) {
                $haystack = strtolower(($row->course->title ?? '').' '.($row->course->category->name ?? ''));

                return str_contains($haystack, strtolower($search));
            })->values();
        }

        return view('admin.users.show', compact('user', 'rows', 'kpis', 'search', 'filter'));
    }
}
