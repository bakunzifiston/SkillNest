<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->get('from') ? Carbon::parse($request->get('from'))->startOfDay() : now()->subYear();
        $dateTo = $request->get('to') ? Carbon::parse($request->get('to'))->endOfDay() : now();

        // --- Revenue (estimated: course price × enrollment count per course)
        $revenueByCourse = Course::query()
            ->select('courses.id', 'courses.title', 'courses.price', 'courses.instructor_id')
            ->selectRaw('COUNT(enrollments.id) as enrollments_count')
            ->selectRaw('COALESCE(courses.price, 0) * COUNT(enrollments.id) as estimated_revenue')
            ->leftJoin('enrollments', 'enrollments.course_id', '=', 'courses.id')
            ->groupBy('courses.id', 'courses.title', 'courses.price', 'courses.instructor_id')
            ->orderByDesc('estimated_revenue')
            ->get();
        $totalRevenue = $revenueByCourse->sum('estimated_revenue');

        // --- Enrollment reports (over time for date range)
        $enrollmentsOverTime = Enrollment::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $totalEnrollmentsInPeriod = Enrollment::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $enrollmentsByCourse = Course::withCount('enrollments')
            ->orderByDesc('enrollments_count')
            ->get();

        // --- Course performance (completion rate, avg %)
        $coursesWithLessons = Course::with(['chapters.lessons'])->get();
        $coursePerformance = $coursesWithLessons->map(function (Course $course) {
            $lessonIds = $course->chapters->flatMap->lessons->pluck('id');
            $totalLessons = $lessonIds->count();
            $enrollments = $course->enrollments()->pluck('user_id');
            if ($enrollments->isEmpty() || $totalLessons === 0) {
                return (object) [
                    'course' => $course,
                    'enrollments_count' => 0,
                    'total_lessons' => $totalLessons,
                    'completed_count' => 0,
                    'completion_rate_percent' => 0,
                    'avg_completion_percent' => 0,
                ];
            }
            $completionsByUser = LessonCompletion::whereIn('user_id', $enrollments)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->groupBy('user_id');
            $completedAll = 0;
            $sumPercent = 0;
            foreach ($enrollments as $uid) {
                $done = $completionsByUser->get($uid, collect())->count();
                $pct = $totalLessons > 0 ? (int) round(($done / $totalLessons) * 100) : 0;
                $sumPercent += $pct;
                if ($done >= $totalLessons) {
                    $completedAll++;
                }
            }
            $avgPercent = $enrollments->count() > 0 ? round($sumPercent / $enrollments->count(), 1) : 0;
            $completionRate = $enrollments->count() > 0 ? round(($completedAll / $enrollments->count()) * 100, 1) : 0;
            return (object) [
                'course' => $course,
                'enrollments_count' => $enrollments->count(),
                'total_lessons' => $totalLessons,
                'completed_count' => $completedAll,
                'completion_rate_percent' => $completionRate,
                'avg_completion_percent' => $avgPercent,
            ];
        })->sortByDesc('enrollments_count')->values();

        // --- Instructor earnings (sum of estimated revenue for their courses)
        $instructorEarnings = Instructor::with('courses')->get()->map(function (Instructor $instructor) {
            $courses = $instructor->courses;
            $totalEnrollments = 0;
            $earnings = 0;
            foreach ($courses as $c) {
                $count = $c->enrollments()->count();
                $totalEnrollments += $count;
                $earnings += (float) $c->price * $count;
            }
            return (object) [
                'instructor' => $instructor,
                'courses_count' => $courses->count(),
                'total_enrollments' => $totalEnrollments,
                'estimated_earnings' => $earnings,
            ];
        })->sortByDesc('estimated_earnings')->values();

        // --- Student engagement
        $totalStudents = User::whereNull('is_admin')->orWhere('is_admin', false)->count();
        $studentsWithCompletions = User::whereHas('lessonCompletions')->count();
        $studentsWithQuizAttempts = User::whereHas('quizAttempts')->count();
        $recentEnrollmentsCount = Enrollment::where('created_at', '>=', now()->subDays(7))->count();
        $recentCompletionsCount = LessonCompletion::where('completed_at', '>=', now()->subDays(7))->count();
        $recentQuizAttemptsCount = QuizAttempt::where('submitted_at', '>=', now()->subDays(7))->count();

        // --- Most popular courses
        $mostPopularCourses = Course::withCount('enrollments')
            ->with('category')
            ->orderByDesc('enrollments_count')
            ->take(15)
            ->get();

        return view('admin.reports.index', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalRevenue' => $totalRevenue,
            'revenueByCourse' => $revenueByCourse,
            'enrollmentsOverTime' => $enrollmentsOverTime,
            'totalEnrollmentsInPeriod' => $totalEnrollmentsInPeriod,
            'enrollmentsByCourse' => $enrollmentsByCourse,
            'coursePerformance' => $coursePerformance,
            'instructorEarnings' => $instructorEarnings,
            'totalStudents' => $totalStudents,
            'studentsWithCompletions' => $studentsWithCompletions,
            'studentsWithQuizAttempts' => $studentsWithQuizAttempts,
            'recentEnrollmentsCount' => $recentEnrollmentsCount,
            'recentCompletionsCount' => $recentCompletionsCount,
            'recentQuizAttemptsCount' => $recentQuizAttemptsCount,
            'mostPopularCourses' => $mostPopularCourses,
        ]);
    }
}
