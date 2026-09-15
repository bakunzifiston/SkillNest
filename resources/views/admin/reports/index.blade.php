@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('header', 'Reports & Analytics')

@section('content')
    @php
        $kpiStyles = function (string $tone): array {
            return match ($tone) {
                'accent' => [
                    'card' => 'bg-gradient-to-br from-accent-light to-white border-accent-muted/70',
                    'icon' => 'bg-accent text-white',
                    'value' => 'text-accent-darker',
                    'label' => 'text-accent-dark',
                    'hint' => 'text-accent-dark/70',
                ],
                'success' => [
                    'card' => 'bg-gradient-to-br from-success-light to-white border-success-muted/70',
                    'icon' => 'bg-success text-white',
                    'value' => 'text-success-darker',
                    'label' => 'text-success-dark',
                    'hint' => 'text-success-dark/70',
                ],
                'slate' => [
                    'card' => 'bg-gradient-to-br from-slate-100 to-white border-slate-200',
                    'icon' => 'bg-navy text-white',
                    'value' => 'text-navy',
                    'label' => 'text-slate-600',
                    'hint' => 'text-slate-500',
                ],
                default => [
                    'card' => 'bg-gradient-to-br from-primary-light to-white border-primary-muted/70',
                    'icon' => 'bg-primary text-white',
                    'value' => 'text-primary-darker',
                    'label' => 'text-primary',
                    'hint' => 'text-primary-dark/70',
                ],
            };
        };
    @endphp

    <form method="get" action="{{ route('admin.reports.index') }}" class="mb-5 rounded-2xl border border-slate-200 bg-white px-4 py-3">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="from" class="block text-xs text-slate-500 mb-1">From</label>
                <input type="date" name="from" id="from" value="{{ $dateFrom->format('Y-m-d') }}" class="rounded-xl border-slate-200 text-sm">
            </div>
            <div>
                <label for="to" class="block text-xs text-slate-500 mb-1">To</label>
                <input type="date" name="to" id="to" value="{{ $dateTo->format('Y-m-d') }}" class="rounded-xl border-slate-200 text-sm">
            </div>
            <button type="submit" class="admin-btn-accent">Apply</button>
            <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">Reset</a>
        </div>
    </form>

    <section class="mb-5" aria-labelledby="reports-kpi-heading">
        <h2 id="reports-kpi-heading" class="sr-only">Key metrics</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($kpis as $kpi)
                @php $styles = $kpiStyles($kpi['tone'] ?? 'primary'); @endphp
                <div class="rounded-2xl border px-4 py-4 {{ $styles['card'] }}">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $styles['icon'] }}">
                            @include('admin.partials.dashboard-icon', ['icon' => $kpi['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider {{ $styles['label'] }}">{{ $kpi['label'] }}</p>
                            <p class="mt-1 font-display font-bold text-2xl tabular-nums leading-none {{ $styles['value'] }}">
                                {{ $kpi['prefix'] ?? '' }}{{ number_format($kpi['value'], $kpi['decimals'] ?? 0) }}{{ $kpi['suffix'] ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-8" aria-labelledby="engagement-heading">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h2 id="engagement-heading" class="font-display font-semibold text-sm text-navy">Engagement</h2>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach($engagementKpis as $kpi)
                @php $styles = $kpiStyles($kpi['tone'] ?? 'primary'); @endphp
                <div class="rounded-2xl border px-3 py-3 {{ $styles['card'] }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl {{ $styles['icon'] }}">
                            @include('admin.partials.dashboard-icon', ['icon' => $kpi['icon'], 'class' => 'h-4 w-4'])
                        </span>
                    </div>
                    <p class="mt-2.5 font-display font-bold text-xl tabular-nums leading-none {{ $styles['value'] }}">
                        {{ number_format($kpi['value']) }}
                    </p>
                    <p class="mt-1.5 text-[11px] font-semibold truncate {{ $styles['label'] }}">{{ $kpi['label'] }}</p>
                    @if(!empty($kpi['hint']))
                        <p class="mt-0.5 text-[10px] truncate {{ $styles['hint'] }}">{{ $kpi['hint'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-8">
        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Most popular courses</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">#</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Enrolled</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($mostPopularCourses as $i => $c)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 text-slate-400 tabular-nums">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-navy truncate max-w-[14rem]" title="{{ $c->title }}">{{ $c->title }}</td>
                                <td class="px-4 py-3">
                                    @if($c->category)
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium">{{ $c->category->name }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">{{ $c->enrollments_count }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">No courses yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Enrollments by month</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($totalEnrollmentsInPeriod) }} in selected period</p>
            </div>
            <div class="p-4">
                @if($enrollmentsOverTime->isEmpty())
                    <p class="text-sm text-slate-500 py-6 text-center">No enrollments in this period.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($enrollmentsOverTime as $e)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-accent-light text-accent-darker text-xs font-medium">
                                {{ $e->month }}
                                <span class="tabular-nums font-bold">{{ $e->count }}</span>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="border-t border-slate-100 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                            <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">All-time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($enrollmentsByCourse->take(10) as $c)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-4 py-3 font-medium text-navy truncate max-w-[16rem]" title="{{ $c->title }}">{{ $c->title }}</td>
                                <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $c->enrollments_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="mb-8 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 flex flex-wrap items-baseline justify-between gap-2">
            <div>
                <h2 class="font-display font-semibold text-sm text-navy">Revenue by course</h2>
                <p class="text-[11px] text-slate-400 mt-0.5">Estimated from price × enrollments</p>
            </div>
            <p class="font-display font-bold text-lg text-accent-darker tabular-nums">${{ number_format($totalRevenue, 0) }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Price</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Enrollments</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Est. revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($revenueByCourse as $r)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy truncate max-w-[18rem]" title="{{ $r->title }}">{{ $r->title }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if(($r->price ?? 0) > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">${{ number_format($r->price, 0) }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Free</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $r->enrollments_count }}</td>
                            <td class="px-4 py-3 text-right font-medium text-navy tabular-nums">${{ number_format($r->estimated_revenue ?? 0, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">No revenue data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-8 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h2 class="font-display font-semibold text-sm text-navy">Instructor earnings</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Estimated from course price × enrollments</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Instructor</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Courses</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Enrollments</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Est. earnings</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instructorEarnings as $e)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $e->instructor->name }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $e->courses_count }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium tabular-nums">{{ $e->total_enrollments }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-navy tabular-nums">${{ number_format($e->estimated_earnings, 0) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">No instructors yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="mb-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h2 class="font-display font-semibold text-sm text-navy">Course performance</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Completion rate = finished all lessons · Avg % = mean progress across enrolled students</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Enrolled</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Lessons</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Completed all</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Completion</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Avg %</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($coursePerformance as $p)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy truncate max-w-[18rem]" title="{{ $p->course->title }}">{{ $p->course->title }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $p->enrollments_count }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $p->total_lessons }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $p->completed_count }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium tabular-nums">
                                    {{ $p->completion_rate_percent }}%
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-slate-600">{{ $p->avg_completion_percent }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">No course performance data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
