@extends('layouts.admin')

@section('title', 'Import students')
@section('header', 'Import students')

@section('content')
    <div class="max-w-5xl space-y-6">
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <p class="text-sm text-slate-600 leading-relaxed">
                Upload a course progress CSV for <span class="font-medium text-slate-800">any course</span>. Students are matched by email — existing users are enrolled, new accounts get a temporary password (use Forgot password to sign in). Progress uses <span class="font-medium text-slate-800">% Completed</span> mapped onto the selected course’s lessons. Safe to re-run for the same or different courses.
            </p>

            <ol class="mt-4 text-sm text-slate-600 list-decimal pl-5 space-y-1">
                <li>Export the student progress CSV from your previous platform.</li>
                <li>Select the SkillNest course that should receive those enrollments.</li>
                <li>Preview, then import. Repeat with another CSV for the next course.</li>
            </ol>

            @if(session('success'))
                <div class="mt-4 p-4 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
            @endif

            @if(!empty($summary['errors']))
                <div class="mt-4 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200 text-sm">
                    <p class="font-medium mb-2">Some rows had errors:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($summary['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="post" enctype="multipart/form-data" class="mt-6 space-y-5" id="import-form">
                @csrf

                <div>
                    <label for="course_id" class="block text-sm font-medium text-slate-700">Target course</label>
                    <select name="course_id" id="course_id" required class="mt-1 block w-full max-w-xl rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
                        <option value="">Select the course to enroll into…</option>
                        @foreach($courses as $course)
                            @php $lessonCount = $course->totalLessonsCount(); @endphp
                            <option value="{{ $course->id }}" @selected(old('course_id', $selectedCourseId ?? null) == $course->id)>
                                {{ $course->title }} ({{ $lessonCount }} {{ Str::plural('lesson', $lessonCount) }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Choose a different course each time you import another progress export.</p>
                    @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="csv" class="block text-sm font-medium text-slate-700">Progress CSV</label>
                    <input type="file" name="csv" id="csv" accept=".csv,text/csv" class="mt-1 block w-full max-w-xl text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-primary-light file:text-primary file:font-medium hover:file:bg-primary-muted">
                    <p class="mt-1 text-xs text-slate-500">Required columns: First Name, Last Name, Email, % Completed. Extra lesson/content columns are detected automatically. Max 5&nbsp;MB.</p>
                    @error('csv')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    @if(!empty($preview))
                        <p class="mt-2 text-xs text-success-dark">CSV loaded for preview. You can import without re-selecting the file, or upload a new one.</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" formaction="{{ route('admin.imports.students.preview') }}" class="admin-btn-secondary">Preview import</button>
                    <button
                        type="submit"
                        formaction="{{ route('admin.imports.students.store') }}"
                        class="admin-btn-accent"
                        @if(empty($preview)) onclick="return confirm('Import without preview? This will create users and enrollments immediately.')" @else onclick="return confirm('Import {{ count($preview) }} rows into the selected course?')" @endif
                    >Import now</button>
                </div>
            </form>
        </div>

        @if(!empty($preview))
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-display font-semibold text-navy">Preview</h2>
                        <p class="text-sm text-slate-500 mt-0.5">
                            {{ count($preview) }} rows
                            @if(!empty($selectedCourse))
                                → {{ $selectedCourse->title }}
                                ({{ $selectedCourse->totalLessonsCount() }} {{ Str::plural('lesson', $selectedCourse->totalLessonsCount()) }})
                            @endif
                        </p>
                        @if(!empty($contentColumns))
                            <p class="text-xs text-slate-500 mt-1">
                                Detected content columns: {{ implode(', ', $contentColumns) }}
                            </p>
                        @endif
                    </div>
                    @php
                        $counts = collect($preview)->countBy('action');
                    @endphp
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-full bg-primary-light text-primary">Create: {{ $counts->get('create', 0) }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">Enroll existing: {{ $counts->get('enroll_existing', 0) }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-accent-light text-accent-darker">Already enrolled: {{ $counts->get('already_enrolled', 0) }}</span>
                        <span class="px-2.5 py-1 rounded-full bg-red-50 text-red-700">Skip: {{ $counts->get('skip', 0) }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Progress %</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Action</th>
                                <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Progress mapping</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($preview as $row)
                                <tr class="hover:bg-slate-50/70 {{ !empty($row['error']) ? 'bg-red-50/40' : '' }}">
                                    <td class="px-4 py-3 font-medium text-navy">{{ $row['name'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row['email'] ?: '—' }}</td>
                                    <td class="px-4 py-3 tabular-nums text-slate-600">{{ $row['percent_completed'] ?? 0 }}%</td>
                                    <td class="px-4 py-3">
                                        <span class="text-slate-800">{{ $row['action_label'] }}</span>
                                        @if(!empty($row['error']))
                                            <span class="block text-xs text-red-600 mt-0.5">{{ $row['error'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $row['progress_note'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
