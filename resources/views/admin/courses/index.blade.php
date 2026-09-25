@extends('layouts.admin')

@section('title', 'Courses')
@section('header', 'Courses')

@section('content')
    <section class="mb-5" aria-labelledby="courses-kpi-heading">
        <h2 id="courses-kpi-heading" class="sr-only">Courses overview</h2>
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
        <form action="{{ route('admin.courses.index') }}" method="get" class="flex flex-wrap gap-2 w-full lg:max-w-3xl">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by title or instructor..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="category_id" class="rounded-xl border-slate-300 text-sm min-w-[10rem]">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((int) ($categoryId ?? 0) === (int) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border-slate-300 text-sm min-w-[9rem]">
                <option value="">All statuses</option>
                <option value="published" @selected(($status ?? '') === 'published')>Published</option>
                <option value="draft" @selected(($status ?? '') === 'draft')>Draft</option>
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || !empty($categoryId) || !empty($status))
                <a href="{{ route('admin.courses.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @adminCan('courses', 'create')
            <a href="{{ route('admin.courses.create') }}" class="admin-btn-accent shrink-0">Add course</a>
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
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Price</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Students</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary text-sm font-semibold uppercase">
                                        {{ \Illuminate\Support\Str::substr($course->title, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-navy truncate max-w-[16rem]" title="{{ $course->title }}">{{ $course->title }}</p>
                                        @if($course->instructor)
                                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $course->instructor->name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($course->category)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium">{{ $course->category->name }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($course->status === 'published')
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Published</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Draft</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if(($course->price ?? 0) > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">${{ number_format($course->price, 0) }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums text-slate-600 whitespace-nowrap">{{ $course->enrollments_count }}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $course->duration ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('courses', 'edit')
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="admin-btn-secondary">Edit</a>
                                        <a href="{{ route('admin.courses.edit', $course) }}?tab=curriculum" class="admin-btn-secondary">Curriculum</a>
                                        <form action="{{ route('admin.courses.status', $course) }}" method="post">
                                            @csrf
                                            @method('PATCH')
                                            @if($course->status === 'published')
                                                <input type="hidden" name="status" value="draft">
                                                <button type="submit" class="admin-btn-secondary">Unpublish</button>
                                            @else
                                                <input type="hidden" name="status" value="published">
                                                <button type="submit" class="admin-btn-accent">Publish</button>
                                            @endif
                                        </form>
                                    @endadminCan
                                    @adminCan('courses', 'delete')
                                        <form action="{{ route('admin.courses.destroy', $course) }}" method="post" onsubmit="return confirm('Delete this course?');">
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
                                    @if(!empty($search) || !empty($categoryId) || !empty($status))
                                        No courses match your filters.
                                    @else
                                        No courses yet.
                                    @endif
                                </p>
                                @if(empty($search) && empty($categoryId) && empty($status))
                                    @adminCan('courses', 'create')
                                        <a href="{{ route('admin.courses.create') }}" class="admin-btn-accent">Add course</a>
                                    @endadminCan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
