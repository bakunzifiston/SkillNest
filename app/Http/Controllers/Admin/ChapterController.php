<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function index(Course $course): View
    {
        $course->load(['chapters.lessons']);
        return view('admin.chapters.index', compact('course'));
    }

    public function create(Course $course): View
    {
        return view('admin.chapters.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate(['title' => 'required|string|max:255']);
        $validated['course_id'] = $course->id;
        $validated['sort_order'] = $course->chapters()->max('sort_order') + 1;
        Chapter::create($validated);
        return redirect()->route('admin.courses.edit', $course)->with('success', 'Chapter added.')->with('tab', 'curriculum');
    }

    public function edit(Chapter $chapter): View
    {
        $chapter->load('course');
        return view('admin.chapters.edit', compact('chapter'));
    }

    public function update(Request $request, Chapter $chapter): RedirectResponse
    {
        $validated = $request->validate(['title' => 'required|string|max:255']);
        $chapter->update($validated);
        return redirect()->route('admin.courses.edit', $chapter->course)->with('success', 'Chapter updated.')->with('tab', 'curriculum');
    }

    public function destroy(Chapter $chapter): RedirectResponse
    {
        $course = $chapter->course;
        $chapter->delete();
        return redirect()->route('admin.courses.edit', $course)->with('success', 'Chapter deleted.')->with('tab', 'curriculum');
    }
}
