<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::with(['category', 'instructor']);
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $courses = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        return view('admin.courses.index', compact('courses', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $instructors = Instructor::orderBy('name')->get();
        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'duration' => 'nullable|string|max:100',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
        ];
        $validated = $request->validate($rules);

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']) . '-' . uniqid();
        $validated['price'] = ($request->boolean('is_free') || empty($request->input('price'))) ? 0 : (float) $request->input('price');
        $validated['level'] = $validated['level'] ?? 'beginner';
        $validated['instructor_id'] = $request->input('instructor_id') ?: null;

        if ($request->hasFile('banner')) {
            $validated['image'] = $request->file('banner')->store('courses', 'public');
        } else {
            $validated['image'] = null;
        }

        unset($validated['banner'], $validated['is_free']);
        $course = Course::create($validated);
        $course->category->increment('courses_count');
        return redirect()->route('admin.courses.edit', $course)->with('success', 'Course created. Add sections and lessons below.')->with('tab', 'curriculum');
    }

    public function edit(Course $course): View
    {
        try {
            $course->load(['chapters' => fn ($q) => $q->orderBy('sort_order'), 'chapters.lessons' => fn ($q) => $q->orderBy('sort_order')]);
        } catch (\Throwable $e) {
            $course->setRelation('chapters', collect());
        }
        $categories = Category::orderBy('name')->get();
        $instructors = Instructor::orderBy('name')->get();
        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'duration' => 'nullable|string|max:100',
            'level' => 'nullable|string|in:beginner,intermediate,advanced',
            'is_free' => 'nullable|boolean',
            'price' => 'nullable|numeric|min:0',
        ];
        $validated = $request->validate($rules);

        $validated['price'] = ($request->boolean('is_free') || $request->input('price') === '' || $request->input('price') === null) ? 0 : (float) $request->input('price');
        $validated['level'] = $validated['level'] ?? 'beginner';
        $validated['instructor_id'] = $request->input('instructor_id') ?: null;

        if ($request->hasFile('banner')) {
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }
            $validated['image'] = $request->file('banner')->store('courses', 'public');
        }
        unset($validated['banner'], $validated['is_free']);

        $oldCategoryId = $course->category_id;
        $course->update($validated);
        if ($oldCategoryId != $course->category_id) {
            Category::where('id', $oldCategoryId)->decrement('courses_count');
            $course->category->increment('courses_count');
        }
        return redirect()->route('admin.courses.edit', $course)->with('success', 'Course updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if ($course->image) {
            Storage::disk('public')->delete($course->image);
        }
        $course->category->decrement('courses_count');
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
