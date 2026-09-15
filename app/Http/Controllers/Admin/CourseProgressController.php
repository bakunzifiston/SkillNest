<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CourseProgressController extends Controller
{
    /**
     * List all courses for student progress (click a course to see enrolled students).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $coursesQuery = Course::withCount('enrollments')
            ->with('category')
            ->orderBy('title');

        if ($search !== '') {
            $coursesQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $courses = $coursesQuery->get();

        $allCourses = Course::withCount('enrollments')->get();
        $totalCourses = $allCourses->count();
        $totalEnrollments = Enrollment::count();
        $uniqueStudents = (int) Enrollment::query()->selectRaw('count(distinct user_id) as aggregate')->value('aggregate');
        $avgPerCourse = $totalCourses > 0
            ? round($totalEnrollments / $totalCourses, 1)
            : 0;

        $kpis = [
            [
                'label' => 'Courses',
                'value' => $totalCourses,
                'icon' => 'book',
                'tone' => 'primary',
            ],
            [
                'label' => 'Enrollments',
                'value' => $totalEnrollments,
                'icon' => 'enroll',
                'tone' => 'accent',
            ],
            [
                'label' => 'Students',
                'value' => $uniqueStudents,
                'icon' => 'users',
                'tone' => 'success',
            ],
            [
                'label' => 'Avg. per course',
                'value' => $avgPerCourse,
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        return view('admin.course-progress.index', compact('courses', 'search', 'kpis'));
    }

    /**
     * Show students enrolled in this course with completion rate, started at, completed at.
     */
    public function show(Request $request, Course $course): View
    {
        $course->load(['chapters.lessons']);
        $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
        $lessonIds = $course->chapters->flatMap->lessons->pluck('id');
        $search = trim((string) $request->get('search', ''));

        $allEnrollments = $course->enrollments()
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $allUserIds = $allEnrollments->pluck('user_id');
        $completionsByUser = LessonCompletion::query()
            ->whereIn('user_id', $allUserIds)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->groupBy('user_id');

        $buildRow = function ($enrollment) use ($totalLessons, $completionsByUser) {
            $userCompletions = $completionsByUser->get($enrollment->user_id, collect());
            $completed = $userCompletions->count();
            $percent = $totalLessons > 0 ? (int) round(($completed / $totalLessons) * 100) : 0;

            return (object) [
                'enrollment' => $enrollment,
                'user' => $enrollment->user,
                'started_at' => $enrollment->created_at,
                'completed_count' => $completed,
                'total_lessons' => $totalLessons,
                'completion_percent' => $percent,
                'completed_at' => $userCompletions->max('completed_at'),
            ];
        };

        /** @var Collection $allRows */
        $allRows = $allEnrollments->map($buildRow);
        $totalEnrolled = $allRows->count();

        $completedStudents = $allRows->filter(fn ($r) => $r->completion_percent >= 100)->count();
        $inProgressStudents = $allRows->filter(fn ($r) => $r->completion_percent > 0 && $r->completion_percent < 100)->count();
        $avgProgress = $totalEnrolled > 0 ? round((float) $allRows->avg('completion_percent'), 1) : 0;

        $rows = $allRows;
        if ($search !== '') {
            $rows = $allRows->filter(function ($row) use ($search) {
                $user = $row->user;
                if (! $user) {
                    return false;
                }
                $haystack = strtolower(implode(' ', [
                    $user->name ?? '',
                    $user->first_name ?? '',
                    $user->last_name ?? '',
                    $user->email ?? '',
                ]));

                return str_contains($haystack, strtolower($search));
            })->values();
        }

        $kpis = [
            [
                'label' => 'Enrolled',
                'value' => $totalEnrolled,
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Completed',
                'value' => $completedStudents,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'In progress',
                'value' => $inProgressStudents,
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

        return view('admin.course-progress.show', compact(
            'course',
            'totalLessons',
            'rows',
            'search',
            'totalEnrolled',
            'kpis'
        ));
    }
}
