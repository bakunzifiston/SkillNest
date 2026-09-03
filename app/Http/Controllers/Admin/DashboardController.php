<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Instructor;
use App\Models\LessonCompletion;
use App\Models\LiveSession;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    /** Completion rate below this % (with enough enrollments) flags a course. */
    private const LOW_COMPLETION_RATE = 30;

    /** Average quiz % below this (with enough attempts) flags a course. */
    private const LOW_QUIZ_AVG = 50;

    /** Minimum enrollments before completion-rate flags apply. */
    private const MIN_ENROLLMENTS_FOR_RATE = 3;

    /** Minimum quiz attempts before quiz-score flags apply. */
    private const MIN_ATTEMPTS_FOR_QUIZ = 3;

    public function __invoke(Request $request)
    {
        $range = $this->resolveRange($request);
        $chartGranularity = $this->resolveGranularity($request, $range['preset']);

        $studentsQuery = User::query()->where('is_admin', false);

        $studentsTotal = (clone $studentsQuery)->where('created_at', '<=', $range['to'])->count();
        $studentsThisPeriod = (clone $studentsQuery)->whereBetween('created_at', [$range['from'], $range['to']])->count();
        $studentsPrevEnd = (clone $studentsQuery)->where('created_at', '<=', $range['prevTo'])->count();

        $activeStudents = $this->activeStudentIds($range['from'], $range['to'])->count();
        $activeStudentsPrev = $this->activeStudentIds($range['prevFrom'], $range['prevTo'])->count();

        $enrollmentsThis = Enrollment::whereBetween('created_at', [$range['from'], $range['to']])->count();
        $enrollmentsPrev = Enrollment::whereBetween('created_at', [$range['prevFrom'], $range['prevTo']])->count();

        $courses = Course::query()
            ->with(['instructor', 'chapters.lessons', 'quizzes'])
            ->withCount('enrollments')
            ->get();

        $allEnrollments = Enrollment::query()->get(['id', 'user_id', 'course_id', 'created_at']);
        $completions = LessonCompletion::query()->get(['user_id', 'lesson_id', 'completed_at']);
        $submittedAttempts = QuizAttempt::query()
            ->whereNotNull('submitted_at')
            ->get(['id', 'user_id', 'quiz_id', 'percentage', 'passed', 'submitted_at', 'created_at']);

        $progressByEnrollment = $this->progressByEnrollment($courses, $allEnrollments, $completions);

        $completedInPeriod = 0;
        $enrollmentsForRate = 0;
        foreach ($allEnrollments as $enrollment) {
            if ($enrollment->created_at->lt($range['from']) || $enrollment->created_at->gt($range['to'])) {
                continue;
            }
            $enrollmentsForRate++;
            $progress = $progressByEnrollment[$enrollment->id] ?? null;
            if ($progress && $progress['status'] === 'completed') {
                $completedInPeriod++;
            }
        }
        $completionRate = $enrollmentsForRate > 0
            ? round(($completedInPeriod / $enrollmentsForRate) * 100, 1)
            : null;

        $attemptsThisPeriod = $submittedAttempts->filter(
            fn ($a) => $a->submitted_at && $a->submitted_at->between($range['from'], $range['to'])
        );
        $avgQuizScore = $attemptsThisPeriod->isNotEmpty()
            ? round((float) $attemptsThisPeriod->avg('percentage'), 1)
            : null;

        $now = now();
        $upcomingSessions = LiveSession::query()
            ->with(['course.instructor'])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at')
            ->take(6)
            ->get();
        $pastSessionsInPeriod = LiveSession::query()
            ->where('scheduled_at', '<', $now)
            ->whereBetween('scheduled_at', [$range['from'], $range['to']])
            ->count();
        $upcomingCount = LiveSession::query()->where('scheduled_at', '>=', $now)->count();

        $instructorsTotal = Instructor::count();
        $courseIdsWithEnrollmentsThisPeriod = Enrollment::query()
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->pluck('course_id')
            ->unique();
        $activeInstructors = Instructor::query()
            ->whereHas('courses', fn ($q) => $q->whereIn('id', $courseIdsWithEnrollmentsThisPeriod))
            ->count();

        $kpis = [
            [
                'key' => 'students',
                'label' => 'Total Students',
                'value' => $studentsTotal,
                'hint' => $studentsThisPeriod.' registered in period',
                'change' => $this->percentChange($studentsTotal, $studentsPrevEnd),
                'href' => route('admin.users.index'),
                'icon' => 'users',
            ],
            [
                'key' => 'active',
                'label' => 'Active Students',
                'value' => $activeStudents,
                'hint' => 'Logged in or completed a lesson in period',
                'change' => $this->percentChange($activeStudents, $activeStudentsPrev),
                'href' => route('admin.users.index'),
                'icon' => 'pulse',
            ],
            [
                'key' => 'courses',
                'label' => 'Total Courses',
                'value' => $courses->count(),
                'hint' => 'Courses are live once created (no draft status)',
                'change' => null,
                'href' => route('admin.courses.index'),
                'icon' => 'book',
            ],
            [
                'key' => 'enrollments',
                'label' => 'Total Enrollments',
                'value' => $enrollmentsThis,
                'hint' => 'In selected period',
                'change' => $this->percentChange($enrollmentsThis, $enrollmentsPrev),
                'href' => route('admin.course-progress.index'),
                'icon' => 'enroll',
            ],
            [
                'key' => 'completion',
                'label' => 'Completion Rate',
                'value' => $completionRate,
                'suffix' => $completionRate !== null ? '%' : null,
                'hint' => $completionRate !== null
                    ? $completedInPeriod.' of '.$enrollmentsForRate.' period enrollments finished all lessons'
                    : 'Needs enrollments with lessons to calculate',
                'change' => null,
                'href' => route('admin.course-progress.index'),
                'icon' => 'check',
            ],
            [
                'key' => 'quiz',
                'label' => 'Average Quiz Score',
                'value' => $avgQuizScore,
                'suffix' => $avgQuizScore !== null ? '%' : null,
                'hint' => $avgQuizScore !== null
                    ? $attemptsThisPeriod->count().' submitted attempts in period'
                    : 'No submitted attempts in this period',
                'change' => null,
                'href' => route('admin.quiz-results.index'),
                'icon' => 'quiz',
            ],
            [
                'key' => 'live',
                'label' => 'Live Sessions',
                'value' => $upcomingCount,
                'hint' => $upcomingCount.' upcoming · '.$pastSessionsInPeriod.' held in period',
                'change' => null,
                'href' => route('admin.live-sessions.index'),
                'icon' => 'live',
            ],
            [
                'key' => 'instructors',
                'label' => 'Instructors',
                'value' => $instructorsTotal,
                'hint' => $activeInstructors.' with enrollments in period',
                'change' => null,
                'href' => route('admin.instructors.index'),
                'icon' => 'instructor',
            ],
        ];

        $enrollmentTrend = $this->timeSeries(
            Enrollment::whereBetween('created_at', [$range['from'], $range['to']])->pluck('created_at'),
            $range['from'],
            $range['to'],
            $chartGranularity
        );
        $engagementStarts = $this->timeSeries(
            Enrollment::whereBetween('created_at', [$range['from'], $range['to']])->pluck('created_at'),
            $range['from'],
            $range['to'],
            $chartGranularity
        );
        $engagementCompletions = $this->timeSeries(
            LessonCompletion::whereBetween('completed_at', [$range['from'], $range['to']])->pluck('completed_at'),
            $range['from'],
            $range['to'],
            $chartGranularity
        );
        $engagementQuizzes = $this->timeSeries(
            $attemptsThisPeriod->pluck('submitted_at'),
            $range['from'],
            $range['to'],
            $chartGranularity
        );

        $progressDistribution = ['not_started' => 0, 'in_progress' => 0, 'completed' => 0];
        foreach ($allEnrollments as $enrollment) {
            $status = $progressByEnrollment[$enrollment->id]['status'] ?? 'not_started';
            $progressDistribution[$status] = ($progressDistribution[$status] ?? 0) + 1;
        }

        $quizStats = $this->quizPerformance($courses, $submittedAttempts);
        $courseRows = $this->coursePerformance($courses, $allEnrollments, $progressByEnrollment, $submittedAttempts);

        $coursesWithActivity = $this->courseIdsWithLearnerActivity(
            $courses,
            $completions,
            $attemptsThisPeriod,
            $allEnrollments,
            $range['from'],
            $range['to']
        );
        $attention = $this->coursesNeedingAttention($courseRows, $coursesWithActivity);
        $topCourses = $courseRows->sortByDesc('enrollments')->take(8)->values();

        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->latest()
            ->take(8)
            ->get()
            ->map(function (Enrollment $enrollment) use ($progressByEnrollment) {
                $progress = $progressByEnrollment[$enrollment->id] ?? ['status' => 'not_started', 'percent' => 0];
                $enrollment->progress_status = $progress['status'];
                $enrollment->progress_percent = $progress['percent'];

                return $enrollment;
            });

        $recentPastSessions = LiveSession::query()
            ->with(['course.instructor'])
            ->where('scheduled_at', '<', $now)
            ->orderByDesc('scheduled_at')
            ->take(4)
            ->get();

        return view('admin.dashboard', [
            'range' => $range,
            'chartGranularity' => $chartGranularity,
            'kpis' => $kpis,
            'enrollmentTrend' => $enrollmentTrend,
            'engagement' => [
                'labels' => $engagementStarts['labels'],
                'starts' => $engagementStarts['counts'],
                'completions' => $engagementCompletions['counts'],
                'quizzes' => $engagementQuizzes['counts'],
            ],
            'progressDistribution' => $progressDistribution,
            'topCourses' => $topCourses,
            'quizOverview' => [
                'total' => Quiz::count(),
                'attempts' => $submittedAttempts->count(),
                'avg' => $submittedAttempts->isNotEmpty() ? round((float) $submittedAttempts->avg('percentage'), 1) : null,
                'passRate' => $submittedAttempts->isNotEmpty()
                    ? round(($submittedAttempts->where('passed', true)->count() / $submittedAttempts->count()) * 100, 1)
                    : null,
            ],
            'quizRows' => $quizStats->take(8),
            'quizChart' => $this->quizChartData($quizStats),
            'lowestQuizzes' => $quizStats->filter(fn ($q) => $q['attempts'] >= self::MIN_ATTEMPTS_FOR_QUIZ)->sortBy('avg')->take(3)->values(),
            'upcomingSessions' => $upcomingSessions,
            'recentPastSessions' => $recentPastSessions,
            'recentEnrollments' => $recentEnrollments,
            'recentActivity' => $this->recentActivity($progressByEnrollment, $completions, $courses),
            'attentionCourses' => $attention,
            'thresholds' => [
                'low_completion_rate' => self::LOW_COMPLETION_RATE,
                'low_quiz_avg' => self::LOW_QUIZ_AVG,
                'min_enrollments' => self::MIN_ENROLLMENTS_FOR_RATE,
                'min_attempts' => self::MIN_ATTEMPTS_FOR_QUIZ,
            ],
        ]);
    }

    /**
     * @return array{preset: string, from: Carbon, to: Carbon, prevFrom: Carbon, prevTo: Carbon, label: string}
     */
    private function resolveRange(Request $request): array
    {
        $preset = $request->get('range', 'year');
        $now = now()->endOfDay();

        switch ($preset) {
            case 'today':
                $from = now()->startOfDay();
                $to = $now;
                $label = 'Today';
                break;
            case '7d':
                $from = now()->subDays(6)->startOfDay();
                $to = $now;
                $label = 'Last 7 days';
                break;
            case '90d':
                $from = now()->subDays(89)->startOfDay();
                $to = $now;
                $label = 'Last 90 days';
                break;
            case 'year':
                $from = now()->startOfYear();
                $to = $now;
                $label = 'This year';
                break;
            case 'custom':
                try {
                    $from = $request->filled('from') ? Carbon::parse($request->get('from'))->startOfDay() : now()->subDays(29)->startOfDay();
                    $to = $request->filled('to') ? Carbon::parse($request->get('to'))->endOfDay() : $now;
                } catch (\Throwable) {
                    $from = now()->subDays(29)->startOfDay();
                    $to = $now;
                }
                if ($from->gt($to)) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                $label = $from->toFormattedDateString().' – '.$to->toFormattedDateString();
                break;
            case '30d':
                $from = now()->subDays(29)->startOfDay();
                $to = $now;
                $label = 'Last 30 days';
                break;
            default:
                $preset = 'year';
                $from = now()->startOfYear();
                $to = $now;
                $label = 'This year';
        }

        $seconds = max(1, $from->diffInSeconds($to));
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subSeconds($seconds);

        return compact('preset', 'from', 'to', 'prevFrom', 'prevTo', 'label');
    }

    private function resolveGranularity(Request $request, string $preset): string
    {
        $allowed = ['weekly', 'monthly', 'yearly'];
        $requested = $request->get('chart_period');
        if (in_array($requested, $allowed, true)) {
            return $requested;
        }

        return match ($preset) {
            'today', '7d' => 'weekly',
            'year' => 'monthly',
            default => 'monthly',
        };
    }

    private function percentChange(int $current, int $previous): ?float
    {
        if ($previous === 0) {
            return null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function activeStudentIds(Carbon $from, Carbon $to): Collection
    {
        $ids = collect();
        $ids = $ids->merge(
            User::query()->where('is_admin', false)->whereBetween('last_login_at', [$from, $to])->pluck('id')
        );
        $ids = $ids->merge(
            LessonCompletion::query()->whereBetween('completed_at', [$from, $to])->pluck('user_id')
        );
        $ids = $ids->merge(
            QuizAttempt::query()->whereBetween('submitted_at', [$from, $to])->pluck('user_id')
        );
        $ids = $ids->merge(
            Enrollment::query()->whereBetween('created_at', [$from, $to])->pluck('user_id')
        );

        return $ids->unique()->filter();
    }

    /**
     * @return array<int, array{status: string, percent: int, done: int, total: int}>
     */
    private function progressByEnrollment(Collection $courses, Collection $enrollments, Collection $completions): array
    {
        $lessonsByCourse = [];
        foreach ($courses as $course) {
            $lessonsByCourse[$course->id] = $course->chapters->flatMap->lessons->pluck('id')->all();
        }

        $doneByUserLesson = [];
        foreach ($completions as $row) {
            $doneByUserLesson[$row->user_id][$row->lesson_id] = true;
        }

        $map = [];
        foreach ($enrollments as $enrollment) {
            $lessonIds = $lessonsByCourse[$enrollment->course_id] ?? [];
            $total = count($lessonIds);
            $done = 0;
            foreach ($lessonIds as $lessonId) {
                if (! empty($doneByUserLesson[$enrollment->user_id][$lessonId])) {
                    $done++;
                }
            }
            $percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;
            $status = 'not_started';
            if ($total > 0 && $done >= $total) {
                $status = 'completed';
            } elseif ($done > 0) {
                $status = 'in_progress';
            }
            $map[$enrollment->id] = compact('status', 'percent', 'done', 'total');
        }

        return $map;
    }

    /**
     * @param  Collection<int, Carbon|null>  $timestamps
     * @return array{labels: list<string>, counts: list<int>}
     */
    private function timeSeries(Collection $timestamps, Carbon $from, Carbon $to, string $granularity): array
    {
        $cursor = match ($granularity) {
            'weekly' => $from->copy()->startOfWeek(Carbon::MONDAY),
            'yearly' => $from->copy()->startOfYear(),
            default => $from->copy()->startOfMonth(),
        };
        $keys = [];
        $labels = [];
        $counts = [];

        while ($cursor->lte($to)) {
            $key = $this->bucketKey($cursor, $granularity);
            if (! isset($keys[$key])) {
                $keys[$key] = count($labels);
                $labels[] = $this->bucketLabel($cursor, $granularity);
                $counts[] = 0;
            }
            $cursor = match ($granularity) {
                'weekly' => $cursor->copy()->addWeek(),
                'yearly' => $cursor->copy()->addYear(),
                default => $cursor->copy()->addMonth(),
            };
        }

        foreach ($timestamps as $ts) {
            if (! $ts) {
                continue;
            }
            $dt = $ts instanceof Carbon ? $ts : Carbon::parse($ts);
            $key = $this->bucketKey($dt, $granularity);
            if (isset($keys[$key])) {
                $counts[$keys[$key]]++;
            }
        }

        return compact('labels', 'counts');
    }

    private function bucketKey(Carbon $dt, string $granularity): string
    {
        return match ($granularity) {
            'weekly' => $dt->isoFormat('GGGG-[W]WW'),
            'yearly' => $dt->format('Y'),
            default => $dt->format('Y-m'),
        };
    }

    private function bucketLabel(Carbon $dt, string $granularity): string
    {
        return match ($granularity) {
            'weekly' => 'W'.$dt->isoWeek.' '.$dt->format('M'),
            'yearly' => $dt->format('Y'),
            default => $dt->format('M Y'),
        };
    }

    private function coursePerformance(Collection $courses, Collection $enrollments, array $progressByEnrollment, Collection $attempts): Collection
    {
        $enrollmentsByCourse = $enrollments->groupBy('course_id');
        $attemptsByQuiz = $attempts->groupBy('quiz_id');

        return $courses->map(function (Course $course) use ($enrollmentsByCourse, $progressByEnrollment, $attemptsByQuiz) {
            $courseEnrollments = $enrollmentsByCourse->get($course->id, collect());
            $inProgress = 0;
            $completed = 0;
            foreach ($courseEnrollments as $enrollment) {
                $status = $progressByEnrollment[$enrollment->id]['status'] ?? 'not_started';
                if ($status === 'completed') {
                    $completed++;
                } elseif ($status === 'in_progress') {
                    $inProgress++;
                }
            }
            $total = $courseEnrollments->count();
            $quizIds = $course->quizzes->pluck('id');
            $courseAttempts = $attemptsByQuiz->only($quizIds->all())->flatten();
            $avgQuiz = $courseAttempts->isNotEmpty() ? round((float) $courseAttempts->avg('percentage'), 1) : null;
            $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : null;

            return [
                'course' => $course,
                'enrollments' => $total,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'completion_rate' => $completionRate,
                'avg_quiz' => $avgQuiz,
                'quiz_attempts' => $courseAttempts->count(),
            ];
        })->values();
    }

    private function quizPerformance(Collection $courses, Collection $attempts): Collection
    {
        $attemptsByQuiz = $attempts->groupBy('quiz_id');
        $rows = collect();
        foreach ($courses as $course) {
            foreach ($course->quizzes as $quiz) {
                $set = $attemptsByQuiz->get($quiz->id, collect());
                $rows->push([
                    'quiz' => $quiz,
                    'course' => $course,
                    'attempts' => $set->count(),
                    'avg' => $set->isNotEmpty() ? round((float) $set->avg('percentage'), 1) : null,
                    'pass_rate' => $set->isNotEmpty()
                        ? round(($set->where('passed', true)->count() / $set->count()) * 100, 1)
                        : null,
                ]);
            }
        }

        return $rows->sortByDesc('attempts')->values();
    }

    private function quizChartData(Collection $quizStats): array
    {
        $withScores = $quizStats->filter(fn ($q) => $q['avg'] !== null)->take(8)->values();

        return [
            'labels' => $withScores->map(fn ($q) => \Illuminate\Support\Str::limit($q['quiz']->title, 28))->all(),
            'scores' => $withScores->pluck('avg')->all(),
        ];
    }

    /**
     * @param  Collection<int, int|string>  $coursesWithActivity
     */
    private function coursesNeedingAttention(Collection $courseRows, Collection $coursesWithActivity): Collection
    {
        return $courseRows->map(function (array $row) use ($coursesWithActivity) {
            $reasons = [];
            if ($row['enrollments'] === 0) {
                $reasons[] = 'No enrollments';
            }
            if (
                $row['enrollments'] >= self::MIN_ENROLLMENTS_FOR_RATE
                && $row['completion_rate'] !== null
                && $row['completion_rate'] < self::LOW_COMPLETION_RATE
            ) {
                $reasons[] = 'Completion rate below '.self::LOW_COMPLETION_RATE.'%';
            }
            if (
                $row['quiz_attempts'] >= self::MIN_ATTEMPTS_FOR_QUIZ
                && $row['avg_quiz'] !== null
                && $row['avg_quiz'] < self::LOW_QUIZ_AVG
            ) {
                $reasons[] = 'Average quiz score below '.self::LOW_QUIZ_AVG.'%';
            }
            if ($row['enrollments'] > 0 && ! $coursesWithActivity->contains($row['course']->id)) {
                $reasons[] = 'No learner activity in selected period';
            }

            $row['reasons'] = $reasons;

            return $row;
        })->filter(fn (array $row) => $row['reasons'] !== [])
            ->sortBy('enrollments')
            ->take(8)
            ->values();
    }

    private function courseIdsWithLearnerActivity(
        Collection $courses,
        Collection $completions,
        Collection $attemptsThisPeriod,
        Collection $enrollments,
        Carbon $from,
        Carbon $to
    ): Collection {
        $lessonToCourse = [];
        $quizToCourse = [];
        foreach ($courses as $course) {
            foreach ($course->chapters as $chapter) {
                foreach ($chapter->lessons as $lesson) {
                    $lessonToCourse[$lesson->id] = $course->id;
                }
            }
            foreach ($course->quizzes as $quiz) {
                $quizToCourse[$quiz->id] = $course->id;
            }
        }

        $ids = collect();
        foreach ($completions as $row) {
            if (! $row->completed_at || ! $row->completed_at->between($from, $to)) {
                continue;
            }
            if (isset($lessonToCourse[$row->lesson_id])) {
                $ids->push($lessonToCourse[$row->lesson_id]);
            }
        }
        foreach ($attemptsThisPeriod as $attempt) {
            if (isset($quizToCourse[$attempt->quiz_id])) {
                $ids->push($quizToCourse[$attempt->quiz_id]);
            }
        }
        foreach ($enrollments as $enrollment) {
            if ($enrollment->created_at && $enrollment->created_at->between($from, $to)) {
                $ids->push($enrollment->course_id);
            }
        }

        return $ids->unique()->values();
    }

    private function recentActivity(array $progressByEnrollment, Collection $completions, Collection $courses): Collection
    {
        $items = collect();

        foreach (User::query()->where('is_admin', false)->latest()->take(8)->get() as $user) {
            $items->push([
                'at' => $user->created_at,
                'icon' => 'user',
                'text' => $user->name.' registered',
            ]);
        }
        foreach (Enrollment::with(['user', 'course'])->latest()->take(8)->get() as $enrollment) {
            $items->push([
                'at' => $enrollment->created_at,
                'icon' => 'enroll',
                'text' => ($enrollment->user->name ?? 'A student').' enrolled in '.($enrollment->course->title ?? 'a course'),
            ]);
        }

        $lessonIdsByCourse = [];
        foreach ($courses as $course) {
            $lessonIdsByCourse[$course->id] = $course->chapters->flatMap->lessons->pluck('id')->all();
        }
        $latestCompletion = [];
        foreach ($completions as $row) {
            if (! $row->completed_at) {
                continue;
            }
            $key = $row->user_id.':'.$row->lesson_id;
            if (! isset($latestCompletion[$key]) || $row->completed_at->gt($latestCompletion[$key])) {
                $latestCompletion[$key] = $row->completed_at;
            }
        }
        $completedEnrollments = Enrollment::with(['user', 'course'])
            ->latest()
            ->take(40)
            ->get()
            ->filter(fn (Enrollment $enrollment) => ($progressByEnrollment[$enrollment->id]['status'] ?? null) === 'completed')
            ->take(8);
        foreach ($completedEnrollments as $enrollment) {
            $at = $enrollment->created_at;
            foreach ($lessonIdsByCourse[$enrollment->course_id] ?? [] as $lessonId) {
                $key = $enrollment->user_id.':'.$lessonId;
                if (isset($latestCompletion[$key]) && $latestCompletion[$key]->gt($at)) {
                    $at = $latestCompletion[$key];
                }
            }
            $items->push([
                'at' => $at,
                'icon' => 'check',
                'text' => ($enrollment->user->name ?? 'A student').' completed '.($enrollment->course->title ?? 'a course'),
            ]);
        }

        foreach (QuizAttempt::with(['user', 'quiz'])->whereNotNull('submitted_at')->latest('submitted_at')->take(8)->get() as $attempt) {
            $items->push([
                'at' => $attempt->submitted_at,
                'icon' => 'quiz',
                'text' => ($attempt->user->name ?? 'A student').' submitted '.($attempt->quiz->title ?? 'a quiz'),
            ]);
        }
        foreach (Course::latest()->take(5)->get() as $course) {
            $items->push([
                'at' => $course->created_at,
                'icon' => 'book',
                'text' => 'Course created: '.$course->title,
            ]);
        }
        foreach (LiveSession::with('course')->latest()->take(5)->get() as $session) {
            $items->push([
                'at' => $session->created_at,
                'icon' => 'live',
                'text' => 'Live session created: '.$session->title,
            ]);
        }
        foreach (Instructor::latest()->take(5)->get() as $instructor) {
            $items->push([
                'at' => $instructor->created_at,
                'icon' => 'instructor',
                'text' => 'Instructor added: '.$instructor->name,
            ]);
        }

        return $items->filter(fn ($i) => $i['at'])->sortByDesc('at')->take(12)->values();
    }
}
