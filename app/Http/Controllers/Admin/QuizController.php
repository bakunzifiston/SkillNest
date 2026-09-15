<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));
        $courseId = $request->filled('course_id') ? (int) $request->get('course_id') : null;

        $query = Quiz::with('course')->withCount('questions');
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$search}%"));
            });
        }

        $quizzes = $query->latest()->paginate(15)->withQueryString();
        $courses = Course::orderBy('title')->get();

        $totalQuizzes = Quiz::count();
        $published = Quiz::query()->where('is_published', true)->count();
        $totalQuestions = Question::count();
        $totalAttempts = QuizAttempt::query()->whereNotNull('submitted_at')->count();

        $kpis = [
            [
                'label' => 'Quizzes',
                'value' => $totalQuizzes,
                'icon' => 'quiz',
                'tone' => 'primary',
            ],
            [
                'label' => 'Published',
                'value' => $published,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Questions',
                'value' => $totalQuestions,
                'icon' => 'book',
                'tone' => 'accent',
            ],
            [
                'label' => 'Attempts',
                'value' => $totalAttempts,
                'icon' => 'users',
                'tone' => 'slate',
            ],
        ];

        return view('admin.quizzes.index', compact(
            'quizzes',
            'courses',
            'kpis',
            'search',
            'courseId'
        ));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->old('course_id', $request->get('course_id'));
        return view('admin.quizzes.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'passing_grade' => 'required|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'is_published' => 'nullable|boolean',
        ]);
        $validated['sort_order'] = Quiz::where('course_id', $validated['course_id'])->max('sort_order') + 1;
        $validated['is_published'] = $request->boolean('is_published', true);
        Quiz::create($validated);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created.');
    }

    public function edit(Quiz $quiz): View
    {
        $quiz->load('course');
        return view('admin.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'passing_grade' => 'required|integer|min:0|max:100',
            'time_limit_minutes' => 'nullable|integer|min:1|max:300',
            'is_published' => 'nullable|boolean',
        ]);
        $validated['is_published'] = $request->boolean('is_published', true);
        $quiz->update($validated);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted.');
    }
}
