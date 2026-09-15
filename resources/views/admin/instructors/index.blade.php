@extends('layouts.admin')

@section('title', 'Instructors')
@section('header', 'Instructors')

@section('content')
    <section class="mb-5" aria-labelledby="instructors-kpi-heading">
        <h2 id="instructors-kpi-heading" class="sr-only">Instructors overview</h2>
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
                                {{ is_float($kpi['value']) ? number_format($kpi['value'], 1) : number_format($kpi['value']) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <form action="{{ route('admin.instructors.index') }}" method="get" class="flex flex-wrap gap-2 w-full sm:max-w-md">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by name or email..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <button type="submit" class="admin-btn-secondary">Search</button>
            @if(!empty($search))
                <a href="{{ route('admin.instructors.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.instructors.create') }}" class="admin-btn-accent shrink-0">Add instructor</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Instructor</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instructors as $instructor)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary text-sm font-semibold uppercase">
                                        {{ \Illuminate\Support\Str::substr($instructor->name, 0, 1) }}
                                    </span>
                                    <span class="font-medium text-navy truncate">{{ $instructor->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                @if($instructor->email)
                                    <span class="block truncate max-w-[18rem]" title="{{ $instructor->email }}">{{ $instructor->email }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($instructor->courses_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium tabular-nums">
                                        {{ $instructor->courses_count }} {{ Str::plural('course', $instructor->courses_count) }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs">No courses</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.instructors.edit', $instructor) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="post" onsubmit="return confirm('Delete this instructor? Their courses will be unassigned.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">
                                    @if(!empty($search))
                                        No instructors match “{{ $search }}”.
                                    @else
                                        No instructors yet.
                                    @endif
                                </p>
                                @if(empty($search))
                                    <a href="{{ route('admin.instructors.create') }}" class="admin-btn-accent">Add instructor</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($instructors->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $instructors->links() }}</div>
        @endif
    </div>
@endsection
