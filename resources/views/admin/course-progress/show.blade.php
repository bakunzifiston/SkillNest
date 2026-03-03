@extends('layouts.admin')

@section('title', 'Student progress — ' . $course->title)
@section('header', 'Student progress: ' . $course->title)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.course-progress.index') }}" class="text-amber-600 hover:underline">← All courses</a>
    </div>
    <p class="mb-4 text-gray-600">{{ $course->title }} — {{ $rows->count() }} student(s) enrolled. Total lessons: {{ $totalLessons }}.</p>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started at</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completion rate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Viewed %</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed at</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rows as $row)
                <tr>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900">{{ $row->user->name }}</div>
                        <div class="text-sm text-gray-500">{{ $row->user->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $row->started_at->format('M j, Y g:i A') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-2 rounded-full bg-gray-200 overflow-hidden">
                                <div class="h-full rounded-full bg-amber-500" style="width: {{ $row->completion_percent }}%"></div>
                            </div>
                            <span class="text-sm text-gray-700">{{ $row->completed_count }} / {{ $row->total_lessons }} ({{ $row->completion_percent }}%)</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $row->viewed_percent }}%</td>
                    <td class="px-6 py-4 text-gray-600">
                        @if($row->completed_at)
                            {{ $row->completed_at->format('M j, Y g:i A') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No students enrolled in this course yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
