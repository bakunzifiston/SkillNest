@extends('layouts.admin')

@section('title', $liveSession->title)
@section('header', 'Live session')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.live-sessions.index') }}" class="text-sm text-slate-500 hover:text-primary inline-flex items-center gap-1">
            <span aria-hidden="true">←</span> All live sessions
        </a>
        <div class="mt-2 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                <h2 class="font-display font-semibold text-lg text-navy">{{ $liveSession->title }}</h2>
                <p class="mt-0.5 text-xs text-slate-400">
                    {{ $liveSession->course->title ?? '—' }}
                    @if($liveSession->course?->instructor)
                        · {{ $liveSession->course->instructor->name }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a href="{{ route('admin.live-sessions.edit', $liveSession) }}" class="admin-btn-secondary">Edit</a>
                <a href="{{ $liveSession->meeting_url }}" target="_blank" rel="noopener" class="admin-btn-accent">Open meeting</a>
            </div>
        </div>
    </div>

    <section class="mb-5" aria-labelledby="live-session-kpi-heading">
        <h2 id="live-session-kpi-heading" class="sr-only">Session overview</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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
                                {{ $kpi['display'] ?? (number_format($kpi['value']).($kpi['suffix'] ?? '')) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h3 class="font-display font-semibold text-sm text-navy">Details</h3>
            </div>
            <dl class="p-4 space-y-3 text-sm">
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Scheduled</dt>
                    <dd class="mt-0.5 text-navy">{{ $liveSession->scheduled_at->format('l, M j, Y · g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ends</dt>
                    <dd class="mt-0.5 text-navy">{{ $endsAt->format('M j, Y · g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Meeting URL</dt>
                    <dd class="mt-0.5">
                        <a href="{{ $liveSession->meeting_url }}" target="_blank" rel="noopener" class="text-primary hover:text-accent break-all">{{ $liveSession->meeting_url }}</a>
                    </dd>
                </div>
                @if($liveSession->meeting_password)
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Meeting password</dt>
                        <dd class="mt-0.5 font-medium text-navy tabular-nums">{{ $liveSession->meeting_password }}</dd>
                    </div>
                @endif
                @if($liveSession->description)
                    <div>
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Description</dt>
                        <dd class="mt-0.5 text-slate-600 whitespace-pre-line">{{ $liveSession->description }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between gap-2">
                <h3 class="font-display font-semibold text-sm text-navy">Invited students</h3>
                <span class="text-xs text-slate-400 tabular-nums">{{ $liveSession->invitedAttendees->count() }}</span>
            </div>
            <div class="overflow-x-auto max-h-80 overflow-y-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 sticky top-0">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Student</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($liveSession->invitedAttendees as $user)
                            @php
                                $name = trim(($user->displayFirstName() ?: '').' '.($user->displayLastName() ?: ''))
                                    ?: ($user->name ?? '—');
                            @endphp
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-primary-light text-primary text-xs font-semibold uppercase">
                                            {{ \Illuminate\Support\Str::substr($user->displayFirstName() ?: $user->email, 0, 1) }}
                                        </span>
                                        <span class="font-medium text-navy truncate">{{ $name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 truncate max-w-[14rem]" title="{{ $user->email }}">{{ $user->email }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-sm text-slate-500">No students invited yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
