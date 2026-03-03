<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(Request $request): View
    {
        $query = Quiz::with('course');
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        $quizzes = $query->latest()->paginate(15);
        $courses = Course::orderBy('title')->get();
        return view('admin.quizzes.index', compact('quizzes', 'courses'));
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
