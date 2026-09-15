@extends('layouts.admin')

@section('title', 'Quizzes')
@section('header', 'Quizzes')

@section('content')
    <section class="mb-5" aria-labelledby="quizzes-kpi-heading">
        <h2 id="quizzes-kpi-heading" class="sr-only">Quizzes overview</h2>
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
                                {{ number_format($kpi['value']) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form action="{{ route('admin.quizzes.index') }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-2xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by quiz or course..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="course_id" class="rounded-xl border-slate-300 text-sm min-w-[10rem]">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" @selected((int) ($courseId ?? 0) === (int) $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || !empty($courseId))
                <a href="{{ route('admin.quizzes.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.quiz-results.index') }}" class="admin-btn-secondary">View results</a>
            <a href="{{ route('admin.quiz-results.export') }}" class="admin-btn-secondary">Export CSV</a>
            <a href="{{ route('admin.quizzes.create') }}" class="admin-btn-accent">Create quiz</a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Quiz</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Passing</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Questions</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($quizzes as $quiz)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-navy truncate max-w-[18rem]" title="{{ $quiz->title }}">{{ $quiz->title }}</p>
                                    @if($quiz->time_limit_minutes)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $quiz->time_limit_minutes }} min limit</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($quiz->course)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium max-w-[14rem] truncate" title="{{ $quiz->course->title }}">
                                        {{ $quiz->course->title }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($quiz->is_published)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Published</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">
                                    {{ $quiz->passing_grade }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($quiz->questions_count > 0)
                                    <span class="tabular-nums text-slate-600">{{ $quiz->questions_count }}</span>
                                @else
                                    <span class="text-slate-300">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="admin-btn-secondary">Questions</a>
                                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="post" onsubmit="return confirm('Delete this quiz and all its questions?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                @if(!empty($search) || !empty($courseId))
                                    <p class="text-sm text-slate-500">No quizzes match your filters.</p>
                                @else
                                    <p class="text-sm text-slate-500 mb-3">No quizzes yet.</p>
                                    <a href="{{ route('admin.quizzes.create') }}" class="admin-btn-accent">Create quiz</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($quizzes->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $quizzes->links() }}</div>
        @endif
    </div>
@endsection
