<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuizResultsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $quizId = $request->filled('quiz_id') ? (int) $request->get('quiz_id') : null;
        $passed = $request->get('passed');
        $passedFilter = in_array($passed, ['0', '1'], true) ? $passed : null;

        $query = QuizAttempt::with(['user', 'quiz.course'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at');

        if ($quizId) {
            $query->where('quiz_id', $quizId);
        }
        if ($passedFilter === '1') {
            $query->where('passed', true);
        } elseif ($passedFilter === '0') {
            $query->where('passed', false);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('quiz', function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$search}%"));
                });
            });
        }

        $attempts = $query->paginate(20)->withQueryString();
        $quizzes = Quiz::with('course')->orderBy('title')->get();

        $base = QuizAttempt::query()->whereNotNull('submitted_at');
        $totalAttempts = (clone $base)->count();
        $passedCount = (clone $base)->where('passed', true)->count();
        $failedCount = (clone $base)->where('passed', false)->count();
        $avgPercent = (clone $base)->avg('percentage');

        $kpis = [
            [
                'label' => 'Attempts',
                'value' => $totalAttempts,
                'icon' => 'quiz',
                'tone' => 'primary',
            ],
            [
                'label' => 'Passed',
                'value' => $passedCount,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Failed',
                'value' => $failedCount,
                'icon' => 'pulse',
                'tone' => 'accent',
            ],
            [
                'label' => 'Avg score',
                'value' => $avgPercent !== null ? (int) round($avgPercent) : 0,
                'suffix' => '%',
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        return view('admin.quiz-results.index', compact(
            'attempts',
            'quizzes',
            'kpis',
            'search',
            'quizId',
            'passedFilter'
        ));
    }

    public function show(QuizAttempt $quizAttempt)
    {
        $quizAttempt->load(['user', 'quiz.course', 'quiz.questions.options', 'answers.question', 'answers.questionOption']);
        return view('admin.quiz-results.show', compact('quizAttempt'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = QuizAttempt::with(['user', 'quiz.course'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at');

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }
        if ($request->get('passed') === '1') {
            $query->where('passed', true);
        } elseif ($request->get('passed') === '0') {
            $query->where('passed', false);
        }
        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('quiz', function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$search}%"));
                });
            });
        }

        $filename = 'quiz-results-' . now()->format('Y-m-d-His') . '.csv';

        return Response::streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Student', 'Email', 'Course', 'Quiz', 'Score', 'Total', 'Percentage', 'Passed', 'Submitted At']);
            $query->chunk(100, function ($attempts) use ($handle) {
                foreach ($attempts as $a) {
                    fputcsv($handle, [
                        $a->user->name ?? '',
                        $a->user->email ?? '',
                        $a->quiz->course->title ?? '',
                        $a->quiz->title ?? '',
                        $a->score,
                        $a->total_points,
                        $a->percentage !== null ? round($a->percentage, 1) . '%' : '',
                        $a->passed ? 'Yes' : 'No',
                        $a->submitted_at?->format('Y-m-d H:i:s') ?? '',
                    ]);
                }
            });
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
