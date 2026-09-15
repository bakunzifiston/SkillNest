@extends('layouts.admin')

@section('title', 'Student progress — ' . $course->title)
@section('header', 'Student progress')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.course-progress.index') }}" class="text-sm text-slate-500 hover:text-primary inline-flex items-center gap-1">
            <span aria-hidden="true">←</span> All courses
        </a>
        <h2 class="mt-1 font-display font-semibold text-lg text-navy">{{ $course->title }}</h2>
        <p class="mt-0.5 text-xs text-slate-400">{{ $totalLessons }} {{ Str::plural('lesson', $totalLessons) }}</p>
    </div>

    <section class="mb-5" aria-labelledby="course-progress-kpi-heading">
        <h2 id="course-progress-kpi-heading" class="sr-only">Course progress overview</h2>
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
                                {{ is_float($kpi['value']) ? number_format($kpi['value'], 1) : number_format($kpi['value']) }}{{ $kpi['suffix'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form action="{{ route('admin.course-progress.show', $course) }}" method="get" class="flex flex-wrap gap-2 w-full sm:max-w-md">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by name or email..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <button type="submit" class="admin-btn-secondary">Search</button>
            @if(!empty($search))
                <a href="{{ route('admin.course-progress.show', $course) }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @if(!empty($search))
            <p class="text-xs text-slate-400">Showing {{ $rows->count() }} of {{ $totalEnrolled }}</p>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Started</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider min-w-[11rem]">Progress</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Completed</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        @php
                            $status = $row->completion_percent >= 100
                                ? 'completed'
                                : ($row->completion_percent > 0 ? 'in_progress' : 'not_started');
                            $studentName = trim(($row->user->displayFirstName() ?: '').' '.($row->user->displayLastName() ?: ''))
                                ?: ($row->user->name ?? '—');
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary text-sm font-semibold uppercase">
                                        {{ \Illuminate\Support\Str::substr($row->user->displayFirstName() ?: $row->user->email, 0, 1) }}
                                    </span>
                                    <span class="font-medium text-navy truncate max-w-[12rem]" title="{{ $studentName }}">{{ $studentName }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <span class="block truncate max-w-[16rem]" title="{{ $row->user->email }}">{{ $row->user->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $row->started_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5 min-w-[10rem]">
                                    <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                        <div
                                            class="h-full rounded-full {{ $status === 'completed' ? 'bg-success' : ($status === 'in_progress' ? 'bg-accent' : 'bg-slate-300') }}"
                                            style="width: {{ min(100, $row->completion_percent) }}%"
                                        ></div>
                                    </div>
                                    <span class="text-xs tabular-nums text-slate-600 whitespace-nowrap w-10 text-right">{{ $row->completion_percent }}%</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-400 tabular-nums">{{ $row->completed_count }}/{{ $row->total_lessons }} lessons</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($status === 'completed')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Completed</span>
                                @elseif($status === 'in_progress')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium">In progress</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Not started</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                @if($row->completed_at)
                                    {{ \Illuminate\Support\Carbon::parse($row->completed_at)->format('M j, Y') }}
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.users.show', $row->user) }}" class="admin-btn-secondary">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                @if(!empty($search))
                                    No students match “{{ $search }}”.
                                @else
                                    No students enrolled in this course yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
