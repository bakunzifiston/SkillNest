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
        $query = QuizAttempt::with(['user', 'quiz.course'])
            ->whereNotNull('submitted_at')
            ->latest('submitted_at');

        if ($request->filled('quiz_id')) {
            $query->where('quiz_id', $request->quiz_id);
        }
        if ($request->filled('passed')) {
            if ($request->passed === '1') {
                $query->where('passed', true);
            } else {
                $query->where('passed', false);
            }
        }

        $attempts = $query->paginate(20);
        $quizzes = Quiz::with('course')->orderBy('title')->get();

        return view('admin.quiz-results.index', compact('attempts', 'quizzes'));
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
