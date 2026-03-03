<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Lesson;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function index(Chapter $chapter): View
    {
        $chapter->load(['lessons', 'course']);
        return view('admin.lessons.index', compact('chapter'));
    }

    public function create(Chapter $chapter): View
    {
        $chapter->load('course');
        return view('admin.lessons.create', compact('chapter'));
    }

    public function store(Request $request, Chapter $chapter): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,youtube,pdf,video',
            'content' => 'nullable|string|max:10000',
            'source_url' => 'nullable|url|max:500',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200', // 50MB
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,webm|max:102400', // 100MB
        ]);

        $validated['chapter_id'] = $chapter->id;
        $validated['sort_order'] = $chapter->lessons()->max('sort_order') + 1;
        $validated['content'] = $request->input('content');
        $validated['source_url'] = $request->input('source_url');

        if ($request->hasFile('pdf_file')) {
            $validated['file_path'] = $request->file('pdf_file')->store('lessons/pdf', 'public');
        } elseif ($request->hasFile('video_file')) {
            $validated['file_path'] = $request->file('video_file')->store('lessons/video', 'public');
        }

        unset($validated['pdf_file'], $validated['video_file']);
        Lesson::create($validated);
        return redirect()->route('admin.courses.edit', $chapter->course)->with('success', 'Lesson added.')->with('tab', 'curriculum');
    }

    public function edit(Lesson $lesson): View
    {
        $lesson->load(['chapter.course']);
        return view('admin.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:text,youtube,pdf,video',
            'content' => 'nullable|string|max:10000',
            'source_url' => 'nullable|url|max:500',
            'pdf_file' => 'nullable|file|mimes:pdf|max:51200',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,webm|max:102400',
        ]);

        $validated['content'] = $request->input('content');
        $validated['source_url'] = $request->input('source_url');

        if ($request->hasFile('pdf_file')) {
            if ($lesson->file_path) {
                Storage::disk('public')->delete($lesson->file_path);
            }
            $validated['file_path'] = $request->file('pdf_file')->store('lessons/pdf', 'public');
        } elseif ($request->hasFile('video_file')) {
            if ($lesson->file_path) {
                Storage::disk('public')->delete($lesson->file_path);
            }
            $validated['file_path'] = $request->file('video_file')->store('lessons/video', 'public');
        }

        unset($validated['pdf_file'], $validated['video_file']);
        $lesson->update($validated);
        return redirect()->route('admin.courses.edit', $lesson->chapter->course)->with('success', 'Lesson updated.')->with('tab', 'curriculum');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        if ($lesson->file_path) {
            Storage::disk('public')->delete($lesson->file_path);
        }
        $course = $lesson->chapter->course;
        $lesson->delete();
        return redirect()->route('admin.courses.edit', $course)->with('success', 'Lesson deleted.')->with('tab', 'curriculum');
    }
}
