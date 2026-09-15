@extends('layouts.admin')

@section('title', 'Quiz Results')
@section('header', 'Quiz Results')

@section('content')
    <section class="mb-5" aria-labelledby="quiz-results-kpi-heading">
        <h2 id="quiz-results-kpi-heading" class="sr-only">Quiz results overview</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($kpis as $kpi)
                @php
                    $styles = match ($kpi['tone'] ?? 'primary') {
                        'accent' => [
                            'card' => 'bg-gradient-to-br from-accent-light to-white border-accent-muted/70',
                            'icon' => 'bg-accent text-white',
                            'value' => 'text-accent-darker',
                            'label' => 'text-accent-dark',
                        ],
                        'success' => [
                            'card' => 'bg-gradient-to-br from-success-light to-white border-success-muted/70',
                            'icon' => 'bg-success text-white',
                            'value' => 'text-success-darker',
                            'label' => 'text-success-dark',
                        ],
                        'slate' => [
                            'card' => 'bg-gradient-to-br from-slate-100 to-white border-slate-200',
                            'icon' => 'bg-navy text-white',
                            'value' => 'text-navy',
                            'label' => 'text-slate-600',
                        ],
                        default => [
                            'card' => 'bg-gradient-to-br from-primary-light to-white border-primary-muted/70',
                            'icon' => 'bg-primary text-white',
                            'value' => 'text-primary-darker',
                            'label' => 'text-primary',
                        ],
                    };
                @endphp
                <div class="rounded-2xl border px-4 py-4 {{ $styles['card'] }}">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $styles['icon'] }}">
                            @include('admin.partials.dashboard-icon', ['icon' => $kpi['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider {{ $styles['label'] }}">{{ $kpi['label'] }}</p>
                            <p class="mt-1 font-display font-bold text-2xl tabular-nums leading-none {{ $styles['value'] }}">
                                {{ number_format($kpi['value']) }}{{ $kpi['suffix'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form action="{{ route('admin.quiz-results.index') }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-3xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search student, email, quiz..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="quiz_id" class="rounded-xl border-slate-300 text-sm min-w-[10rem]">
                <option value="">All quizzes</option>
                @foreach($quizzes as $q)
                    <option value="{{ $q->id }}" @selected((int) ($quizId ?? 0) === (int) $q->id)>
                        {{ $q->title }}@if($q->course) ({{ $q->course->title }})@endif
                    </option>
                @endforeach
            </select>
            <select name="passed" class="rounded-xl border-slate-300 text-sm min-w-[8rem]">
                <option value="">All results</option>
                <option value="1" @selected(($passedFilter ?? null) === '1')>Passed</option>
                <option value="0" @selected(($passedFilter ?? null) === '0')>Failed</option>
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || !empty($quizId) || $passedFilter !== null)
                <a href="{{ route('admin.quiz-results.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        <a
            href="{{ route('admin.quiz-results.export', array_filter(['quiz_id' => $quizId, 'passed' => $passedFilter, 'search' => $search ?: null])) }}"
            class="admin-btn-accent shrink-0"
        >Export CSV</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Quiz</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Score</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Result</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Submitted</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attempts as $a)
                        @php
                            $studentName = trim(($a->user?->displayFirstName() ?: '').' '.($a->user?->displayLastName() ?: ''))
                                ?: ($a->user->name ?? '—');
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-navy truncate max-w-[14rem]" title="{{ $studentName }}">{{ $studentName }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[14rem]" title="{{ $a->user->email ?? '' }}">{{ $a->user->email ?? '' }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-navy truncate max-w-[16rem]" title="{{ $a->quiz->title ?? '' }}">{{ $a->quiz->title ?? '—' }}</p>
                                    @if($a->quiz?->course)
                                        <p class="text-xs text-slate-400 mt-0.5 truncate max-w-[16rem]">{{ $a->quiz->course->title }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">
                                    {{ $a->percentage !== null ? round($a->percentage, 0) : 0 }}%
                                </span>
                                <span class="ml-1.5 text-xs text-slate-400 tabular-nums">{{ $a->score }}/{{ $a->total_points }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($a->passed)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Passed</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-red-50 text-red-700 text-xs font-medium">Failed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $a->submitted_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.quiz-results.show', $a) }}" class="admin-btn-secondary">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                @if(!empty($search) || !empty($quizId) || $passedFilter !== null)
                                    No results match your filters.
                                @else
                                    No submitted attempts yet.
                                @endif
                            </td>
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
