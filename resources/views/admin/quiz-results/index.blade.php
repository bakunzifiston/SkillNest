@extends('layouts.admin')

@section('title', 'Quiz Results')
@section('header', 'Quiz Results')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">View and export student quiz attempts.</p>
        <a href="{{ route('admin.quiz-results.export') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="admin-btn-accent">Export CSV</a>
    </div>
    <div class="mb-4">
        <form method="get" class="flex flex-wrap gap-2">
            <select name="quiz_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm">
                <option value="">All quizzes</option>
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" {{ request('quiz_id') == $q->id ? 'selected' : '' }}>{{ $q->title }} ({{ $q->course->title ?? '' }})</option>
                @endforeach
            </select>
            <select name="passed" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm">
                <option value="">All results</option>
                <option value="1" {{ request('passed') === '1' ? 'selected' : '' }}>Passed</option>
                <option value="0" {{ request('passed') === '0' ? 'selected' : '' }}>Failed</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course / Quiz</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Score</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Passed</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Submitted</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attempts as $a)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <span class="font-medium text-navy">{{ $a->user->name ?? '—' }}</span>
                                <span class="block text-xs text-slate-500">{{ $a->user->email ?? '' }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $a->quiz->course->title ?? '—' }} / {{ $a->quiz->title }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $a->score }} / {{ $a->total_points }} ({{ $a->percentage !== null ? round($a->percentage, 1) : 0 }}%)</td>
                            <td class="px-4 py-3">
                                @if($a->passed)
                                    <span class="text-success-dark font-medium">Passed</span>
                                @else
                                    <span class="text-red-600">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $a->submitted_at?->format('M j, Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.quiz-results.show', $a) }}" class="admin-btn-secondary">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No submitted attempts yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attempts->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $attempts->links() }}</div>
        @endif
    </div>
@endsection
