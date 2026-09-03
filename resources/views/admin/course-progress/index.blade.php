@extends('layouts.admin')

@section('title', 'Student progress by course')
@section('header', 'Student progress by course')

@section('content')
    <p class="mb-5 text-sm text-slate-500">Open a course to see enrolled students and their completion.</p>
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Students enrolled</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $course->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $course->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $course->enrollments_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.course-progress.show', $course) }}" class="admin-btn-secondary">View students</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No courses yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
