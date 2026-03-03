@extends('layouts.admin')

@section('title', 'Reports & Analytics')
@section('header', 'Reports & Analytics')

@section('content')
    <div class="mb-6">
        <form method="get" class="flex flex-wrap items-end gap-3">
            <div>
                <label for="from" class="block text-xs font-medium text-gray-500">From</label>
                <input type="date" name="from" id="from" value="{{ $dateFrom->format('Y-m-d') }}" class="mt-1 rounded-lg border-gray-300 text-sm">
            </div>
            <div>
                <label for="to" class="block text-xs font-medium text-gray-500">To</label>
                <input type="date" name="to" id="to" value="{{ $dateTo->format('Y-m-d') }}" class="mt-1 rounded-lg border-gray-300 text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600">Apply</button>
        </form>
        <p class="text-xs text-gray-500 mt-2">Date range applies to enrollment-over-time and period totals where relevant.</p>
    </div>

    {{-- Group: Engagement & popularity --}}
    <div class="mb-10">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Engagement & popularity</h2>
        <div class="space-y-6">
            <section class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Student engagement</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ $totalStudents }}</p>
                        <p class="text-sm text-gray-500">Total students (non-admin users)</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ $studentsWithCompletions }}</p>
                        <p class="text-sm text-gray-500">Students with at least one lesson completed</p>
                    </div>
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <p class="text-2xl font-bold text-gray-900">{{ $studentsWithQuizAttempts }}</p>
                        <p class="text-sm text-gray-500">Students with at least one quiz attempt</p>
                    </div>
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-100">
                        <p class="text-2xl font-bold text-amber-700">{{ $recentEnrollmentsCount }}</p>
                        <p class="text-sm text-gray-500">Enrollments (last 7 days)</p>
                    </div>
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-100">
                        <p class="text-2xl font-bold text-amber-700">{{ $recentCompletionsCount }}</p>
                        <p class="text-sm text-gray-500">Lesson completions (last 7 days)</p>
                    </div>
                    <div class="p-4 rounded-lg bg-amber-50 border border-amber-100">
                        <p class="text-2xl font-bold text-amber-700">{{ $recentQuizAttemptsCount }}</p>
                        <p class="text-sm text-gray-500">Quiz attempts submitted (last 7 days)</p>
                    </div>
                </div>
            </section>
            <section class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Most popular courses</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($mostPopularCourses as $i => $c)
                                <tr>
                                    <td class="px-4 py-2 text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $c->title }}</td>
                                    <td class="px-4 py-2 text-gray-600">{{ $c->category->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-amber-600">{{ $c->enrollments_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Group: Revenue & earnings --}}
    <div class="mb-10">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Revenue & earnings</h2>
        <div class="space-y-6">
            <section class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Revenue reports</h3>
                <p class="text-sm text-gray-500 mb-4">Estimated revenue (course price × enrollments). Does not reflect actual payments.</p>
                <div class="mb-4">
                    <span class="text-2xl font-bold text-amber-600">{{ number_format($totalRevenue, 2) }}</span>
                    <span class="text-gray-500 ml-2">Total estimated revenue</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Est. revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($revenueByCourse as $r)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $r->title }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ number_format($r->price ?? 0, 2) }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $r->enrollments_count }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($r->estimated_revenue ?? 0, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
            <section class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Instructor earnings</h3>
                <p class="text-sm text-gray-500 mb-4">Estimated earnings (sum of course price × enrollments for each instructor’s courses).</p>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Courses</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total enrollments</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Est. earnings</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($instructorEarnings as $e)
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $e->instructor->name }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $e->courses_count }}</td>
                                    <td class="px-4 py-2 text-right text-gray-600">{{ $e->total_enrollments }}</td>
                                    <td class="px-4 py-2 text-right font-medium text-amber-600">{{ number_format($e->estimated_earnings, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- Group: Enrollments & growth --}}
    <div class="mb-10">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Enrollments & growth</h2>
        <section class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Enrollment reports</h3>
            <div class="mb-4 flex flex-wrap gap-6">
                <div>
                    <span class="text-xl font-bold text-gray-900">{{ number_format($totalEnrollmentsInPeriod) }}</span>
                    <span class="text-gray-500 ml-2">Enrollments in selected period</span>
                </div>
                <div>
                    <span class="text-xl font-bold text-gray-900">{{ $enrollmentsByCourse->sum('enrollments_count') }}</span>
                    <span class="text-gray-500 ml-2">Total enrollments (all time)</span>
                </div>
            </div>
            <div class="mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Enrollments over time (by month)</h4>
                @if($enrollmentsOverTime->isEmpty())
                    <p class="text-gray-500 text-sm">No enrollments in this period.</p>
                @else
                    <div class="flex flex-wrap gap-4">
                        @foreach($enrollmentsOverTime as $e)
                            <span class="px-3 py-1.5 bg-amber-50 text-amber-800 rounded-lg text-sm">{{ $e->month }}: <strong>{{ $e->count }}</strong></span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($enrollmentsByCourse->take(20) as $c)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $c->title }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $c->enrollments_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Group: Learning & performance --}}
    <div class="mb-10">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Learning & performance</h2>
        <section class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Course performance</h3>
            <p class="text-sm text-gray-500 mb-4">Completion rate = % of enrolled students who completed all lessons. Avg % = average completion across enrolled students.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Enrolled</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Lessons</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Completed all</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Completion rate</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Avg completion %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($coursePerformance as $p)
                            <tr>
                                <td class="px-4 py-2 font-medium text-gray-900">{{ $p->course->title }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $p->enrollments_count }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $p->total_lessons }}</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $p->completed_count }}</td>
                                <td class="px-4 py-2 text-right font-medium">{{ $p->completion_rate_percent }}%</td>
                                <td class="px-4 py-2 text-right text-gray-600">{{ $p->avg_completion_percent }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
