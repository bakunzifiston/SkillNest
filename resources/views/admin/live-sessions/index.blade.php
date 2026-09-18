@extends('layouts.admin')

@section('title', 'Live Sessions')
@section('header', 'Live sessions')

@section('content')
    <section class="mb-5" aria-labelledby="live-sessions-kpi-heading">
        <h2 id="live-sessions-kpi-heading" class="sr-only">Live sessions overview</h2>
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
        <form action="{{ route('admin.live-sessions.index') }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-2xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search sessions or courses..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="course_id" class="rounded-xl border-slate-300 text-sm min-w-[12rem]">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" @selected((int) ($courseId ?? 0) === (int) $c->id)>{{ $c->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || !empty($courseId))
                <a href="{{ route('admin.live-sessions.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @adminCan('live_sessions', 'create')
            <a href="{{ route('admin.live-sessions.create') }}" class="admin-btn-accent shrink-0">Add live session</a>
        @endadminCan
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Session</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Scheduled</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Invitees</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($liveSessions as $session)
                        @php $isUpcoming = $session->scheduled_at->isFuture(); @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <p class="font-medium text-navy truncate max-w-[16rem]" title="{{ $session->title }}">{{ $session->title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($session->course)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium">{{ $session->course->title }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $session->scheduled_at->format('M j, Y · g:i A') }}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $session->duration_minutes }} min</td>
                            <td class="px-4 py-3">
                                @if($isUpcoming)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Upcoming</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Past</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">
                                    {{ $session->invited_attendees_count ?? 0 }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.live-sessions.show', $session) }}" class="admin-btn-secondary">View</a>
                                    @adminCan('live_sessions', 'edit')
                                        <a href="{{ route('admin.live-sessions.edit', $session) }}" class="admin-btn-secondary">Edit</a>
                                    @endadminCan
                                    @adminCan('live_sessions', 'delete')
                                        <form action="{{ route('admin.live-sessions.destroy', $session) }}" method="post" onsubmit="return confirm('Delete this live session?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-danger">Delete</button>
                                        </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">
                                    @if(!empty($search) || !empty($courseId))
                                        No live sessions match your filters.
                                    @else
                                        No live sessions yet.
                                    @endif
                                </p>
                                @if(empty($search) && empty($courseId))
                                    @adminCan('live_sessions', 'create')
                                        <a href="{{ route('admin.live-sessions.create') }}" class="admin-btn-accent">Add live session</a>
                                    @endadminCan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($liveSessions->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $liveSessions->links() }}</div>
        @endif
    </div>
@endsection
