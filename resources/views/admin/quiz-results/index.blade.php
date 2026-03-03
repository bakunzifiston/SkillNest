@extends('layouts.admin')

@section('title', 'Quiz Results')
@section('header', 'Quiz Results')

@section('content')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <p class="text-gray-600">View and export student quiz attempts.</p>
        <a href="{{ route('admin.quiz-results.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Export CSV</a>
    </div>
    <div class="mb-4 flex flex-wrap gap-3">
        <form method="get" class="flex gap-2 items-center flex-wrap">
            <select name="quiz_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All quizzes</option>
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }} ({{ $q->course->title ?? '' }})</option>
                @endforeach
            </select>
            <select name="passed" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All results</option>
                <option value="1" {{ request('passed') === '1' ? 'selected' : '' }}>Passed</option>
                <option value="0" {{ request('passed') === '0' ? 'selected' : '' }}>Failed</option>
            </select>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course / Quiz</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Passed</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($attempts as $a)
                <tr>
                    <td class="px-6 py-4">
                        <span class="font-medium text-gray-900">{{ $a->user->name ?? '—' }}</span>
                        <span class="block text-sm text-gray-500">{{ $a->user->email ?? '' }}</span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $a->quiz->course->title ?? '—' }} / {{ $a->quiz->title }}</td>
                    <td class="px-6 py-4">{{ $a->score }} / {{ $a->total_points }} ({{ $a->percentage !== null ? round($a->percentage, 1) : 0 }}%)</td>
                    <td class="px-6 py-4">
                        @if($a->passed)
                            <span class="text-green-600 font-medium">Passed</span>
                        @else
                            <span class="text-red-600">Failed</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $a->submitted_at?->format('M j, Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.quiz-results.show', $a) }}" class="text-amber-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No submitted attempts yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($attempts->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $attempts->links() }}</div>
        @endif
    </div>
@endsection
