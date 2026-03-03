@extends('layouts.admin')

@section('title', 'Student progress by course')
@section('header', 'Student progress by course')

@section('content')
    <p class="mb-6 text-gray-600">Click a course to see enrolled students and their completion, viewed %, started at, and completed at.</p>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students enrolled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">View progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($courses as $course)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $course->title }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $course->category->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $course->enrollments_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.course-progress.show', $course) }}" class="text-amber-600 hover:underline font-medium">View students</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No courses yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
