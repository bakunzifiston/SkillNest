<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstructorController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $instructorsQuery = Instructor::withCount('courses')->orderBy('name');
        if ($search !== '') {
            $instructorsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $instructors = $instructorsQuery->paginate(15)->withQueryString();

        $all = Instructor::withCount('courses')->get();
        $totalInstructors = $all->count();
        $withCourses = $all->filter(fn ($i) => $i->courses_count > 0)->count();
        $totalCourses = (int) $all->sum('courses_count');
        $avgCourses = $totalInstructors > 0 ? round($totalCourses / $totalInstructors, 1) : 0;

        $kpis = [
            [
                'label' => 'Instructors',
                'value' => $totalInstructors,
                'icon' => 'instructor',
                'tone' => 'primary',
            ],
            [
                'label' => 'With courses',
                'value' => $withCourses,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Courses assigned',
                'value' => $totalCourses,
                'icon' => 'book',
                'tone' => 'accent',
            ],
            [
                'label' => 'Avg. per instructor',
                'value' => $avgCourses,
                'icon' => 'chart',
                'tone' => 'slate',
            ],
        ];

        return view('admin.instructors.index', compact('instructors', 'kpis', 'search'));
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
