<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function show(Course $course, Quiz $quiz): View|RedirectResponse
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('courses.quizzes.show', [$course, $quiz], false));
            return redirect()->route('login');
        }
        if ($quiz->course_id !== $course->id) {
            abort(404);
        }
        if (! auth()->user()->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course)->with('error', 'Enroll in the course to take this quiz.');
        }
        if (! $quiz->is_published) {
            return redirect()->route('courses.show', $course)->with('error', 'This quiz is not available.');
        }
        $quiz->load('questions.options');
        return view('quizzes.show', compact('course', 'quiz'));
    }

    public function start(Course $course, Quiz $quiz): RedirectResponse
    {
        if (! auth()->check() || ! auth()->user()->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course)->with('error', 'Enroll in the course first.');
        }
        if ($quiz->course_id !== $course->id || ! $quiz->is_published) {
            abort(404);
        }
        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'started_at' => now(),
            'total_points' => $quiz->questions()->sum('points'),
        ]);
        return redirect()->route('quizzes.take', $attempt);
    }

    public function take(QuizAttempt $quizAttempt): View|RedirectResponse
    {
        if ($quizAttempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($quizAttempt->submitted_at !== null) {
            return redirect()->route('quizzes.result', $quizAttempt)->with('info', 'You already submitted this attempt.');
        }
        $quizAttempt->load(['quiz.course', 'quiz.questions.options']);
        return view('quizzes.take', compact('quizAttempt'));
    }

    public function submit(Request $request, QuizAttempt $quizAttempt): RedirectResponse
    {
        if ($quizAttempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($quizAttempt->submitted_at !== null) {
            return redirect()->route('quizzes.result', $quizAttempt);
        }
        $quiz = $quizAttempt->quiz;
        $totalPoints = $quiz->questions()->sum('points');
        $score = 0;
        foreach ($quiz->questions as $question) {
            $selectedId = $request->input('question_' . $question->id);
            $correctOption = $question->options->firstWhere('is_correct', true);
            $selectedOption = $selectedId ? $question->options->find($selectedId) : null;
            $isCorrect = $selectedOption && $selectedOption->is_correct;
            $pointsEarned = $isCorrect ? $question->points : 0;
            $score += $pointsEarned;
            QuizAttemptAnswer::create([
                'quiz_attempt_id' => $quizAttempt->id,
                'question_id' => $question->id,
                'question_option_id' => $selectedOption?->id,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]);
        }
        $percentage = $totalPoints > 0 ? round(($score / $totalPoints) * 100, 2) : 0;
        $passed = $percentage >= $quiz->passing_grade;
        $quizAttempt->update([
            'score' => $score,
            'total_points' => $totalPoints,
            'percentage' => $percentage,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);
        return redirect()->route('quizzes.result', $quizAttempt)->with('success', 'Quiz submitted.');
    }

    public function result(QuizAttempt $quizAttempt): View|RedirectResponse
    {
        if ($quizAttempt->user_id !== auth()->id()) {
            abort(403);
        }
        $quizAttempt->load(['quiz.course', 'quiz.questions.options', 'answers.question', 'answers.questionOption']);
        return view('quizzes.result', compact('quizAttempt'));
    }
}
