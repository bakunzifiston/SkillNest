<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BundleController extends Controller
{
    public function index(): View
    {
        $bundles = Bundle::withCount('courses')->latest()->paginate(15);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function create(): View
    {
        return view('admin.bundles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:draft,published,archived',
        ]);
        $validated['slug'] = $validated['slug'] ?: \Illuminate\Support\Str::slug($validated['title']) . '-' . uniqid();
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('bundles', 'public');
        } else {
            $validated['thumbnail'] = null;
        }
        $bundle = Bundle::create($validated);
        return redirect()->route('admin.bundles.edit', $bundle)->with('success', 'Bundle created. Add courses below.');
    }

    public function edit(Bundle $bundle): View
    {
        $bundle->load('courses');
        $coursesNotInBundle = Course::whereNotIn('id', $bundle->courses->pluck('id'))->orderBy('title')->get();
        return view('admin.bundles.edit', compact('bundle', 'coursesNotInBundle'));
    }

    public function update(Request $request, Bundle $bundle): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'status' => 'required|in:draft,published,archived',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = $bundle->slug;
        }
        if ($request->hasFile('thumbnail')) {
            if ($bundle->thumbnail) {
                Storage::disk('public')->delete($bundle->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('bundles', 'public');
        }
        $bundle->update($validated);
        return redirect()->route('admin.bundles.edit', $bundle)->with('success', 'Bundle updated.');
    }

    public function destroy(Bundle $bundle): RedirectResponse
    {
        if ($bundle->thumbnail) {
            Storage::disk('public')->delete($bundle->thumbnail);
        }
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted.');
    }

    public function addCourse(Request $request, Bundle $bundle): RedirectResponse
    {
        $request->validate(['course_id' => 'required|exists:courses,id']);
        $courseId = (int) $request->course_id;
        if ($bundle->courses()->where('course_id', $courseId)->exists()) {
            return back()->with('info', 'Course already in bundle.');
        }
        $maxOrder = $bundle->courses()->max('order') ?? -1;
        $bundle->courses()->attach($courseId, ['order' => $maxOrder + 1]);
        return back()->with('success', 'Course added to bundle.');
    }

    public function removeCourse(Bundle $bundle, Course $course): RedirectResponse
    {
        $bundle->courses()->detach($course->id);
        return back()->with('success', 'Course removed from bundle.');
    }

    public function updateCourseOrder(Request $request, Bundle $bundle): RedirectResponse
    {
        $order = $request->input('order', []);
        if (! is_array($order)) {
            return back();
        }
        foreach ($order as $i => $courseId) {
            $bundle->courses()->updateExistingPivot($courseId, ['order' => $i]);
        }
        return back()->with('success', 'Order updated.');
    }
}
