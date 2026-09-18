@extends('layouts.admin')

@section('title', 'Bundles')
@section('header', 'Bundles')

@section('content')
    <section class="mb-5" aria-labelledby="bundles-kpi-heading">
        <h2 id="bundles-kpi-heading" class="sr-only">Bundles overview</h2>
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
        <form action="{{ route('admin.bundles.index') }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-2xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search bundles..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="status" class="rounded-xl border-slate-300 text-sm min-w-[9rem]">
                <option value="">All statuses</option>
                <option value="published" @selected(($status ?? '') === 'published')>Published</option>
                <option value="draft" @selected(($status ?? '') === 'draft')>Draft</option>
                <option value="archived" @selected(($status ?? '') === 'archived')>Archived</option>
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || !empty($status))
                <a href="{{ route('admin.bundles.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @adminCan('bundles', 'create')
            <a href="{{ route('admin.bundles.create') }}" class="admin-btn-accent shrink-0">Add bundle</a>
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
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Bundle</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bundles as $bundle)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <p class="font-medium text-navy truncate max-w-[18rem]" title="{{ $bundle->title }}">{{ $bundle->title }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-mono">{{ $bundle->slug }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium
                                    @if($bundle->status === 'published') bg-success-light text-success-darker
                                    @elseif($bundle->status === 'archived') bg-slate-100 text-slate-600
                                    @else bg-accent-light text-accent-darker @endif">
                                    {{ ucfirst($bundle->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($bundle->courses_count > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium tabular-nums">
                                        {{ $bundle->courses_count }} {{ Str::plural('course', $bundle->courses_count) }}
                                    </span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs">No courses</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('bundles', 'edit')
                                        <a href="{{ route('admin.bundles.edit', $bundle) }}" class="admin-btn-secondary">Edit</a>
                                    @endadminCan
                                    @adminCan('bundles', 'delete')
                                        <form action="{{ route('admin.bundles.destroy', $bundle) }}" method="post" onsubmit="return confirm('Delete this bundle?');">
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
                            <td colspan="5" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">
                                    @if(!empty($search) || !empty($status))
                                        No bundles match your filters.
                                    @else
                                        No bundles yet.
                                    @endif
                                </p>
                                @if(empty($search) && empty($status))
                                    @adminCan('bundles', 'create')
                                        <a href="{{ route('admin.bundles.create') }}" class="admin-btn-accent">Add bundle</a>
                                    @endadminCan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bundles->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $bundles->links() }}</div>
        @endif
    </div>
@endsection
