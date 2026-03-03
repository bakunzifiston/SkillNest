<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function index(): View
    {
        $instructors = Instructor::withCount('courses')->orderBy('name')->paginate(15);
        return view('admin.instructors.index', compact('instructors'));
    }

    public function create(): View
    {
        return view('admin.instructors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string|max:5000',
        ]);
        Instructor::create($validated);
        return redirect()->route('admin.instructors.index')->with('success', 'Instructor created.');
    }

    public function edit(Instructor $instructor): View
    {
        return view('admin.instructors.edit', compact('instructor'));
    }

    public function update(Request $request, Instructor $instructor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string|max:5000',
        ]);
        $instructor->update($validated);
        return redirect()->route('admin.instructors.index')->with('success', 'Instructor updated.');
    }

    public function destroy(Instructor $instructor): RedirectResponse
    {
        $instructor->courses()->update(['instructor_id' => null]);
        $instructor->delete();
        return redirect()->route('admin.instructors.index')->with('success', 'Instructor deleted.');
    }
}
