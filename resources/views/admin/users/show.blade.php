@extends('layouts.admin')

@section('title', 'Progress — ' . ($user->displayFirstName() ?: $user->name))
@section('header', 'User progress')

@section('content')
    @php
        $studentName = trim(($user->displayFirstName() ?: '').' '.($user->displayLastName() ?: ''))
            ?: ($user->name ?? 'User');
    @endphp

    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-500 hover:text-primary inline-flex items-center gap-1">
            <span aria-hidden="true">←</span> All users
        </a>
        <div class="mt-2 flex flex-wrap items-center gap-3">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $user->is_admin ? 'bg-navy text-white' : 'bg-primary-light text-primary' }} text-sm font-semibold uppercase">
                {{ \Illuminate\Support\Str::substr($user->displayFirstName() ?: $user->email, 0, 1) }}
            </span>
            <div class="min-w-0">
                <h2 class="font-display font-semibold text-lg text-navy truncate">{{ $studentName }}</h2>
                <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
            </div>
            @if($user->is_admin)
                <span class="inline-flex px-2 py-0.5 rounded-md bg-navy text-white text-xs font-medium">Admin</span>
            @else
                <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Student</span>
            @endif
        </div>
        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-400">
            <span>Joined {{ $user->created_at->format('M j, Y') }}</span>
            <span>Last sign in {{ $user->last_login_at ? $user->last_login_at->format('M j, Y') : '—' }}</span>
        </div>
    </div>

    <section class="mb-5" aria-labelledby="user-progress-kpi-heading">
        <h2 id="user-progress-kpi-heading" class="sr-only">Progress overview</h2>
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

    <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <form action="{{ route('admin.users.show', $user) }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-2xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by course or category..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="filter" class="rounded-xl border-slate-300 text-sm min-w-[9rem]">
                <option value="all" @selected(($filter ?? 'all') === 'all')>All courses</option>
                <option value="enrolled" @selected(($filter ?? 'all') === 'enrolled')>Enrolled only</option>
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || ($filter ?? 'all') !== 'all')
                <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider min-w-[12rem]">Progress</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <p class="font-medium text-navy truncate max-w-[18rem]" title="{{ $row->course->title }}">{{ $row->course->title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($row->course->category)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium">{{ $row->course->category->name }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($row->status === 'completed')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Completed</span>
                                @elseif($row->status === 'in_progress')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium">In progress</span>
                                @elseif($row->status === 'not_started')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Not started</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">Not enrolled</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($row->enrolled)
                                    <div class="flex items-center gap-2.5 min-w-[10rem]">
                                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                                            <div
                                                class="h-full rounded-full {{ $row->status === 'completed' ? 'bg-success' : ($row->status === 'in_progress' ? 'bg-accent' : 'bg-slate-300') }}"
                                                style="width: {{ min(100, $row->percent) }}%"
                                            ></div>
                                        </div>
                                        <span class="text-xs tabular-nums text-slate-600 whitespace-nowrap w-10 text-right">{{ $row->percent }}%</span>
                                    </div>
                                    <p class="mt-1 text-[11px] text-slate-400 tabular-nums">{{ $row->completed }}/{{ $row->total_lessons }} lessons</p>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">
                                @if(!empty($search) || ($filter ?? 'all') !== 'all')
                                    No courses match your filters.
                                @else
                                    No courses on the platform yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
