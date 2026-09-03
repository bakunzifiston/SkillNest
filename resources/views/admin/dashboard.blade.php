@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    @php
        $filterParams = request()->only(['range', 'from', 'to', 'chart_period']);
        $dashUrl = fn (array $extra = []) => route('admin.dashboard', array_filter(array_merge($filterParams, $extra)));
        $rangePresets = [
            'today' => 'Today',
            '7d' => 'Last 7 days',
            '30d' => 'Last 30 days',
            '90d' => 'Last 90 days',
            'year' => 'This year',
            'custom' => 'Custom range',
        ];
    @endphp

    <form method="get" action="{{ route('admin.dashboard') }}" class="mb-6 bg-white rounded-xl border border-slate-200 p-3 sm:p-4">
        @if($chartGranularity)
            <input type="hidden" name="chart_period" value="{{ $chartGranularity }}">
        @endif
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 mr-1">Period</span>
            @foreach($rangePresets as $key => $label)
                <a
                    href="{{ $dashUrl(['range' => $key, 'chart_period' => $chartGranularity]) }}"
                    class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $range['preset'] === $key ? 'bg-navy text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >{{ $label }}</a>
            @endforeach
        </div>
        @if($range['preset'] === 'custom')
            <div class="mt-3 flex flex-wrap items-end gap-3">
                <div>
                    <label for="from" class="block text-xs text-slate-500 mb-1">From</label>
                    <input type="date" name="from" id="from" value="{{ $range['from']->toDateString() }}" class="rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label for="to" class="block text-xs text-slate-500 mb-1">To</label>
                    <input type="date" name="to" id="to" value="{{ $range['to']->toDateString() }}" class="rounded-lg border-slate-300 text-sm">
                </div>
                <input type="hidden" name="range" value="custom">
                <button type="submit" class="px-3 py-2 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary-dark">Apply</button>
            </div>
        @endif
        <p class="mt-2 text-xs text-slate-400">Showing {{ $range['label'] }}. Comparisons use the equal-length period before this range. Trends are omitted when the previous period is zero.</p>
    </form>

    <section class="mb-5" aria-labelledby="kpi-heading">
        <h2 id="kpi-heading" class="sr-only">Key metrics</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-2">
            @foreach($kpis as $kpi)
                <a href="{{ $kpi['href'] }}" class="group bg-white rounded-lg border border-slate-200 px-2.5 py-2 hover:border-primary-muted hover:shadow-brand focus:outline-none focus:ring-2 focus:ring-primary transition">
                    <div class="flex items-center justify-between gap-1">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-md bg-primary-light text-primary" aria-hidden="true">
                            @include('admin.partials.dashboard-icon', ['icon' => $kpi['icon'], 'class' => 'h-3.5 w-3.5'])
                        </span>
                        @if($kpi['change'] !== null)
                            <span class="text-[10px] font-semibold tabular-nums {{ $kpi['change'] >= 0 ? 'text-success' : 'text-red-600' }}">
                                {{ $kpi['change'] >= 0 ? '+' : '' }}{{ $kpi['change'] }}%
                            </span>
                        @endif
                    </div>
                    <p class="mt-1.5 font-display font-bold text-lg tabular-nums text-navy leading-none">
                        {{ $kpi['value'] === null ? '—' : number_format($kpi['value'], is_float($kpi['value']) ? 1 : 0) }}{{ $kpi['suffix'] ?? '' }}
                    </p>
                    <p class="mt-0.5 text-[11px] font-medium text-slate-600 truncate">{{ $kpi['label'] }}</p>
                    <p class="text-[10px] text-slate-400 leading-snug line-clamp-2">{{ $kpi['hint'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 lg:grid-cols-5 gap-4" aria-labelledby="analytics-heading">
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <div>
                    <h2 id="analytics-heading" class="font-display font-semibold text-sm text-navy">Enrollment Trends</h2>
                    <p class="text-[11px] text-slate-400">{{ array_sum($enrollmentTrend['counts']) }} enrollments in {{ $range['label'] }}</p>
                </div>
                <div class="inline-flex rounded-lg bg-slate-100 p-0.5">
                    @foreach(['weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $g => $gLabel)
                        <a href="{{ $dashUrl(['chart_period' => $g]) }}" class="px-2 py-1 rounded-md text-[11px] font-medium {{ $chartGranularity === $g ? 'bg-white text-navy shadow-sm' : 'text-slate-500 hover:text-navy' }}">{{ $gLabel }}</a>
                    @endforeach
                </div>
            </div>
            @if(array_sum($enrollmentTrend['counts']) > 0)
                <div class="h-40"><canvas id="enrollmentTrendChart" aria-label="Enrollment trends"></canvas></div>
            @else
                <div class="h-40 flex items-center justify-center text-xs text-slate-500">No enrollments in this period.</div>
            @endif
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4">
            <h2 class="font-display font-semibold text-sm text-navy">Learner Engagement</h2>
            <p class="text-[11px] text-slate-400 mb-3">Course starts, lesson completions, and quiz submissions</p>
            @if(array_sum($engagement['starts']) + array_sum($engagement['completions']) + array_sum($engagement['quizzes']) > 0)
                <div class="h-40"><canvas id="engagementChart" aria-label="Learner engagement"></canvas></div>
            @else
                <div class="h-40 flex items-center justify-center text-xs text-slate-500">No learner activity in this period.</div>
            @endif
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4">
            <h2 class="font-display font-semibold text-sm text-navy">Student Progress</h2>
            <p class="text-[11px] text-slate-400 mb-3">All enrollments: not started, in progress, completed</p>
            @if(array_sum($progressDistribution) > 0)
                <div class="h-40"><canvas id="progressChart" aria-label="Student progress distribution"></canvas></div>
            @else
                <div class="h-40 flex items-center justify-center text-xs text-slate-500">No enrollment progress yet.</div>
            @endif
        </div>
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Course Performance</h2>
                <a href="{{ route('admin.courses.index') }}" class="text-xs font-medium text-primary hover:text-accent">View all courses</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left font-medium px-4 py-2">Course</th>
                            <th class="text-right font-medium px-3 py-2">Enrollments</th>
                            <th class="text-right font-medium px-3 py-2">In progress</th>
                            <th class="text-right font-medium px-3 py-2">Completed</th>
                            <th class="text-right font-medium px-3 py-2">Completion</th>
                            <th class="text-right font-medium px-4 py-2">Avg. quiz</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topCourses as $row)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-2.5">
                                    <a href="{{ route('admin.courses.edit', $row['course']) }}" class="font-medium text-navy hover:text-primary">{{ $row['course']->title }}</a>
                                </td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $row['enrollments'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $row['in_progress'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $row['completed'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $row['completion_rate'] === null ? '—' : $row['completion_rate'].'%' }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $row['avg_quiz'] === null ? '—' : $row['avg_quiz'].'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">No courses yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 lg:grid-cols-5 gap-4">
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4">
            <h2 class="font-display font-semibold text-sm text-navy">Quiz Performance</h2>
            <p class="text-[11px] text-slate-400 mb-2">Average score by quiz (submitted attempts)</p>
            @if(count($quizChart['scores']) > 0)
                <div class="h-32 mb-3"><canvas id="quizChart" aria-label="Average quiz scores"></canvas></div>
            @endif
            <div class="mt-3 grid grid-cols-2 gap-2 text-center">
                <div class="rounded-lg bg-slate-50 p-2">
                    <p class="font-display font-bold text-lg text-navy tabular-nums">{{ $quizOverview['total'] }}</p>
                    <p class="text-[11px] text-slate-500">Quizzes</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-2">
                    <p class="font-display font-bold text-lg text-navy tabular-nums">{{ $quizOverview['attempts'] }}</p>
                    <p class="text-[11px] text-slate-500">Attempts</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-2">
                    <p class="font-display font-bold text-lg text-navy tabular-nums">{{ $quizOverview['avg'] === null ? '—' : $quizOverview['avg'].'%' }}</p>
                    <p class="text-[11px] text-slate-500">Avg. score</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-2">
                    <p class="font-display font-bold text-lg text-navy tabular-nums">{{ $quizOverview['passRate'] === null ? '—' : $quizOverview['passRate'].'%' }}</p>
                    <p class="text-[11px] text-slate-500">Pass rate</p>
                </div>
            </div>
            @if($lowestQuizzes->isNotEmpty())
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Lowest-performing ({{ $thresholds['min_attempts'] }}+ attempts)</p>
                <ul class="mt-1 space-y-1">
                    @foreach($lowestQuizzes as $q)
                        <li class="text-xs text-slate-600 truncate">{{ $q['quiz']->title }} · {{ $q['avg'] }}%</li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="lg:col-span-3 bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Assessments</h2>
                <a href="{{ route('admin.quiz-results.index') }}" class="text-xs font-medium text-primary hover:text-accent">Quiz results</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left font-medium px-4 py-2">Quiz</th>
                            <th class="text-left font-medium px-3 py-2">Course</th>
                            <th class="text-right font-medium px-3 py-2">Attempts</th>
                            <th class="text-right font-medium px-3 py-2">Avg. score</th>
                            <th class="text-right font-medium px-4 py-2">Pass rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($quizRows as $q)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-navy">{{ $q['quiz']->title }}</td>
                                <td class="px-3 py-2.5 text-slate-500 truncate max-w-[12rem]">{{ $q['course']->title }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $q['attempts'] }}</td>
                                <td class="px-3 py-2.5 text-right tabular-nums">{{ $q['avg'] === null ? '—' : $q['avg'].'%' }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $q['pass_rate'] === null ? '—' : $q['pass_rate'].'%' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">No quizzes yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mb-8 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
            <h2 class="font-display font-semibold text-sm text-navy">Live Sessions</h2>
            <a href="{{ route('admin.live-sessions.index') }}" class="text-xs font-medium text-primary hover:text-accent">View all sessions</a>
        </div>
        <div class="grid md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-100">
            <div class="p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-accent mb-2">Upcoming</p>
                @forelse($upcomingSessions as $session)
                    <div class="py-2 border-b border-slate-50 last:border-0">
                        <p class="text-sm font-medium text-navy">{{ $session->title }}</p>
                        <p class="text-[11px] text-slate-500">{{ $session->course->title ?? '—' }} · {{ $session->course->instructor->name ?? '—' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $session->scheduled_at->format('M j, Y · g:i A') }} · {{ $session->duration_minutes }} min</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4">No upcoming sessions.</p>
                @endforelse
            </div>
            <div class="p-4">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Recently held</p>
                @forelse($recentPastSessions as $session)
                    <div class="py-2 border-b border-slate-50 last:border-0">
                        <p class="text-sm font-medium text-navy">{{ $session->title }}</p>
                        <p class="text-[11px] text-slate-500">{{ $session->course->title ?? '—' }}</p>
                        <p class="text-[11px] text-slate-400">{{ $session->scheduled_at->format('M j, Y · g:i A') }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4">No completed sessions yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mb-8 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Recent Activity</h2>
            </div>
            <ul class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                @forelse($recentActivity as $item)
                    <li class="px-4 py-2.5 flex items-start gap-2">
                        <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-md bg-primary-light text-primary" aria-hidden="true">
                            @include('admin.partials.dashboard-icon', ['icon' => $item['icon']])
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs text-navy">{{ $item['text'] }}</p>
                            <p class="text-[11px] text-slate-400">{{ $item['at']?->diffForHumans() }}</p>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-xs text-slate-500">No recent activity.</li>
                @endforelse
            </ul>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <h2 class="font-display font-semibold text-sm text-navy">Recent Enrollments</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-medium text-primary hover:text-accent">View all students</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="text-left font-medium px-4 py-2">Student</th>
                            <th class="text-left font-medium px-3 py-2">Course</th>
                            <th class="text-left font-medium px-3 py-2">Date</th>
                            <th class="text-right font-medium px-4 py-2">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentEnrollments as $enrollment)
                            @php
                                $status = $enrollment->progress_status ?? 'not_started';
                                $statusLabel = ['not_started' => 'Not started', 'in_progress' => 'In progress', 'completed' => 'Completed'][$status];
                            @endphp
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-navy">{{ $enrollment->user->name ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-slate-600 truncate max-w-[10rem]">{{ $enrollment->course->title ?? '—' }}</td>
                                <td class="px-3 py-2.5 text-slate-400 whitespace-nowrap">{{ $enrollment->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium
                                        {{ $status === 'completed' ? 'bg-success-light text-success-darker' : ($status === 'in_progress' ? 'bg-accent-light text-accent-darker' : 'bg-slate-100 text-slate-500') }}">
                                        {{ $statusLabel }} · {{ $enrollment->progress_percent ?? 0 }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">No enrollments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="mb-4 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100">
            <h2 class="font-display font-semibold text-sm text-navy">Courses Requiring Attention</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">
                Flags: no enrollments; no lesson, quiz, or enrollment activity in the selected period; completion rate below {{ $thresholds['low_completion_rate'] }}% with {{ $thresholds['min_enrollments'] }}+ enrollments; quiz average below {{ $thresholds['low_quiz_avg'] }}% with {{ $thresholds['min_attempts'] }}+ attempts.
            </p>
        </div>
        @if($attentionCourses->isEmpty())
            <p class="px-4 py-8 text-center text-xs text-slate-500">No courses currently match these attention rules.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($attentionCourses as $row)
                    <li class="px-4 py-3 flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <a href="{{ route('admin.courses.edit', $row['course']) }}" class="text-sm font-medium text-navy hover:text-primary">{{ $row['course']->title }}</a>
                            <p class="text-[11px] text-slate-500">{{ implode(' · ', $row['reasons']) }}</p>
                        </div>
                        <span class="text-[11px] text-slate-400">{{ $row['enrollments'] }} enrolled</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = getComputedStyle(document.documentElement);
            const accent = root.getPropertyValue('--color-accent').trim() || '#F16029';
            const primary = root.getPropertyValue('--color-primary').trim() || '#19499B';
            const navy = root.getPropertyValue('--color-navy').trim() || '#0B1B33';
            const success = root.getPropertyValue('--color-success').trim() || '#1D9E75';
            const tick = { color: '#64748b', font: { size: 10 } };

            const trend = document.getElementById('enrollmentTrendChart');
            if (trend) {
                new Chart(trend, {
                    type: 'line',
                    data: {
                        labels: @json($enrollmentTrend['labels']),
                        datasets: [{
                            label: 'Enrollments',
                            data: @json($enrollmentTrend['counts']),
                            borderColor: primary,
                            backgroundColor: 'rgba(25, 73, 155, 0.12)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                            pointBackgroundColor: primary,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: tick },
                            y: { beginAtZero: true, ticks: { ...tick, stepSize: 1 }, grid: { color: 'rgba(15,23,42,0.06)' } }
                        }
                    }
                });
            }

            const engagement = document.getElementById('engagementChart');
            if (engagement) {
                new Chart(engagement, {
                    type: 'bar',
                    data: {
                        labels: @json($engagement['labels']),
                        datasets: [
                            { label: 'Course starts', data: @json($engagement['starts']), backgroundColor: primary },
                            { label: 'Lesson completions', data: @json($engagement['completions']), backgroundColor: accent },
                            { label: 'Quiz submissions', data: @json($engagement['quizzes']), backgroundColor: success },
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } } },
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: tick },
                            y: { stacked: true, beginAtZero: true, ticks: { ...tick, stepSize: 1 }, grid: { color: 'rgba(15,23,42,0.06)' } }
                        }
                    }
                });
            }

            const quiz = document.getElementById('quizChart');
            if (quiz) {
                new Chart(quiz, {
                    type: 'bar',
                    data: {
                        labels: @json($quizChart['labels']),
                        datasets: [{
                            label: 'Avg. score %',
                            data: @json($quizChart['scores']),
                            backgroundColor: primary,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, max: 100, ticks: tick, grid: { color: 'rgba(15,23,42,0.06)' } },
                            y: { grid: { display: false }, ticks: tick }
                        }
                    }
                });
            }

            const progress = document.getElementById('progressChart');
            if (progress) {
                new Chart(progress, {
                    type: 'doughnut',
                    data: {
                        labels: ['Not started', 'In progress', 'Completed'],
                        datasets: [{
                            data: [
                                {{ (int) $progressDistribution['not_started'] }},
                                {{ (int) $progressDistribution['in_progress'] }},
                                {{ (int) $progressDistribution['completed'] }},
                            ],
                            backgroundColor: ['#C9D6EE', accent, success],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: { legend: { position: 'bottom', labels: { boxWidth: 8, font: { size: 10 } } } }
                    }
                });
            }
        });
    </script>
@endsection
