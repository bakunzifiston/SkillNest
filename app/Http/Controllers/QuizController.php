<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            return redirect()->route('courses.show', $course)->with('error', __('Enroll in the course to take this quiz.'));
        }
        if (! $quiz->is_published) {
            return redirect()->route('courses.show', $course)->with('error', __('This quiz is not available.'));
        }
        $quiz->load('questions.options');
        $state = $quiz->attemptStateFor(auth()->user());
        return view('quizzes.show', compact('course', 'quiz', 'state'));
    }

    public function start(Course $course, Quiz $quiz): RedirectResponse
    {
        if (! auth()->check() || ! auth()->user()->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course)->with('error', __('Enroll in the course first.'));
        }
        if ($quiz->course_id !== $course->id || ! $quiz->is_published) {
            abort(404);
        }
        $user = auth()->user();
        $blocked = null;

        // One at a time: a double click must not create two attempts or slip past the maximum.
        $attempt = DB::transaction(function () use ($quiz, $user, &$blocked) {
            Quiz::whereKey($quiz->id)->lockForUpdate()->first();
            $state = $quiz->attemptStateFor($user);

            if ($state['unfinished']) {
                return $state['unfinished'];                                  // resume instead of starting over
            }
            if (! $state['canStart']) {
                $blocked = $state['reason'];

                return null;
            }

            return QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'started_at' => now(),
                'total_points' => $quiz->questions()->sum('points'),
            ]);
        });

        if ($attempt === null) {
            $message = $blocked === 'passed'
                ? __('You already passed this quiz and cannot retake it.')
                : __('You have used all :max attempts for this quiz.', ['max' => $quiz->max_attempts]);

            return redirect()->route('courses.quizzes.show', [$course, $quiz])->with('error', $message);
        }

        return redirect()->route('quizzes.take', $attempt);
    }

    public function take(QuizAttempt $quizAttempt): View|RedirectResponse
    {
        if ($quizAttempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($quizAttempt->submitted_at !== null) {
            return redirect()->route('quizzes.result', $quizAttempt)->with('info', __('You already submitted this attempt.'));
        }
        $quizAttempt->load(['quiz.course', 'quiz.questions.options']);
        if ($quizAttempt->isExpired()) {
            $quizAttempt->closeAsTimedOut();

            return redirect()->route('quizzes.result', $quizAttempt)->with('info', __('Time is up. Your attempt was closed.'));
        }
        $remainingSeconds = $quizAttempt->remainingSeconds();
        $draft = $quizAttempt->draft_answers ?? [];
        return view('quizzes.take', compact('quizAttempt', 'remainingSeconds', 'draft'));
    }

    /** Autosave while answering: keeps the answers on the server (resume, closed tab, and auto-submit at the deadline). */
    public function autosave(Request $request, QuizAttempt $quizAttempt): JsonResponse
    {
        if ($quizAttempt->user_id !== auth()->id()) {
            abort(403);
        }
        if ($quizAttempt->submitted_at !== null) {
            return response()->json(['closed' => true, 'redirect' => route('quizzes.result', $quizAttempt)], 409);
        }
        $quizAttempt->loadMissing('quiz');
        // The time ran out while the student was answering: submit what was saved, and tell the page.
        if ($quizAttempt->isExpired()) {
            $quizAttempt->closeAsTimedOut();

            return response()->json(['closed' => true, 'redirect' => route('quizzes.result', $quizAttempt)], 409);
        }

        $request->validate(['answers' => ['nullable', 'array']]);
        $quizAttempt->saveDraft($request->input('answers', []));

        return response()->json(['saved' => true, 'remaining' => $quizAttempt->remainingSeconds()]);
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
        // The time limit is enforced here, not only in the browser: answers that arrive too late are not graded.
        if ($quizAttempt->isExpired(QuizAttempt::GRACE_SECONDS)) {
            $quizAttempt->closeAsTimedOut();

            return redirect()->route('quizzes.result', $quizAttempt)->with('info', __('Time is up. Your attempt was closed.'));
        }
        $selected = [];
        foreach ($quiz->questions as $question) {
            $selected[$question->id] = $request->input('question_'.$question->id);
        }
        $quizAttempt->submitAnswers($selected);
        return redirect()->route('quizzes.result', $quizAttempt)->with('success', __('Quiz submitted.'));
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
