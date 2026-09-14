<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CourseProgressCsvImporter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class StudentImportController extends Controller
{
    public function create(): View
    {
        return view('admin.imports.students', [
            'courses' => $this->coursesForSelect(),
            'preview' => null,
            'contentColumns' => [],
            'summary' => session('import_summary'),
        ]);
    }

    public function preview(Request $request, CourseProgressCsvImporter $importer): View
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'csv' => ['required', 'file', 'max:5120', 'extensions:csv'],
        ]);

        $course = Course::query()
            ->with(['chapters.lessons'])
            ->findOrFail($validated['course_id']);

        $storedPath = $request->file('csv')->store('imports/progress-csv');
        $absolutePath = Storage::path($storedPath);

        try {
            $parsed = $importer->parse($absolutePath);
            $preview = $importer->preview($parsed['rows'], $course, $parsed['content_columns']);
        } catch (InvalidArgumentException|RuntimeException $e) {
            Storage::delete($storedPath);
            throw ValidationException::withMessages(['csv' => $e->getMessage()]);
        }

        session([
            'progress_csv_import_path' => $storedPath,
            'progress_csv_import_course_id' => $course->id,
        ]);

        return view('admin.imports.students', [
            'courses' => $this->coursesForSelect(),
            'preview' => $preview,
            'contentColumns' => $parsed['content_columns'],
            'selectedCourseId' => $course->id,
            'selectedCourse' => $course,
            'summary' => null,
        ]);
    }

    public function store(Request $request, CourseProgressCsvImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'csv' => ['nullable', 'file', 'max:5120', 'extensions:csv'],
        ]);

        $course = Course::query()
            ->with(['chapters.lessons'])
            ->findOrFail($validated['course_id']);

        $storedPath = session('progress_csv_import_path');

        if ($request->hasFile('csv')) {
            if ($storedPath) {
                Storage::delete($storedPath);
            }
            $storedPath = $request->file('csv')->store('imports/progress-csv');
        }

        if (! $storedPath || ! Storage::exists($storedPath)) {
            throw ValidationException::withMessages([
                'csv' => 'Please upload a CSV and run Preview before importing, or upload the CSV again.',
            ]);
        }

        $absolutePath = Storage::path($storedPath);

        try {
            $parsed = $importer->parse($absolutePath);
            $summary = $importer->import($parsed['rows'], $course);
        } catch (InvalidArgumentException|RuntimeException $e) {
            throw ValidationException::withMessages(['csv' => $e->getMessage()]);
        } finally {
            Storage::delete($storedPath);
            session()->forget(['progress_csv_import_path', 'progress_csv_import_course_id']);
        }

        return redirect()
            ->route('admin.imports.students.create')
            ->with('success', $this->formatSuccessMessage($summary, $course))
            ->with('import_summary', $summary);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Course>
     */
    protected function coursesForSelect()
    {
        return Course::query()
            ->with(['chapters.lessons'])
            ->orderBy('title')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    protected function formatSuccessMessage(array $summary, Course $course): string
    {
        return sprintf(
            'Imported into “%s”: %d new users, %d existing users, %d enrollments created, %d already enrolled, %d lesson completions added, %d rows skipped.',
            $course->title,
            $summary['created_users'],
            $summary['existing_users'],
            $summary['enrollments_created'],
            $summary['enrollments_skipped'],
            $summary['lessons_completed'],
            $summary['skipped_rows']
        );
    }
}
