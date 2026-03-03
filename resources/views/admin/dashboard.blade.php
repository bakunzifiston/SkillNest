@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Super Admin Dashboard')

@section('content')
    {{-- Stats grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Categories</h3>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['categories'] }}</p>
            <p class="mt-1 text-sm text-gray-500">Manage course categories</p>
        </a>
        <a href="{{ route('admin.courses.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Courses</h3>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['courses'] }}</p>
            <p class="mt-1 text-sm text-gray-500">Manage courses</p>
        </a>
        <a href="{{ route('admin.bundles.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Bundles</h3>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['bundles'] }}</p>
            <p class="mt-1 text-sm text-gray-500">Manage course bundles</p>
        </a>
        <a href="{{ route('admin.users.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Users</h3>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['users'] }}</p>
            <p class="mt-1 text-sm text-gray-500">View users & student progress</p>
        </a>
        <a href="{{ route('admin.course-progress.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Student progress</h3>
            <p class="mt-1 text-sm text-gray-500">By course: completion, viewed %, started & completed at</p>
        </a>
        <a href="{{ route('admin.settings.edit') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Settings</h3>
            <p class="mt-1 text-sm text-gray-500">Upload logo & site options</p>
        </a>
        <a href="{{ route('admin.partners.index') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition">
            <h3 class="font-semibold text-gray-800">Partner logos</h3>
            <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['partners'] }}</p>
            <p class="mt-1 text-sm text-gray-500">Trusted by section on home</p>
        </a>
        <a href="{{ route('home') }}" class="block p-6 bg-white rounded-xl border border-gray-200 hover:shadow-md transition" target="_blank">
            <h3 class="font-semibold text-gray-800">View site</h3>
            <p class="mt-1 text-sm text-gray-500">Open frontend in new tab</p>
        </a>
    </div>

    {{-- Chart: Enrollments (monthly/weekly) --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-8">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Enrollments over time</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.dashboard', ['chart_period' => 'weekly']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($chartPeriod ?? 'monthly') === 'weekly' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Weekly</a>
                <a href="{{ route('admin.dashboard', ['chart_period' => 'monthly']) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ($chartPeriod ?? 'monthly') === 'monthly' ? 'bg-amber-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Monthly</a>
            </div>
        </div>
        <div class="h-64">
            <canvas id="enrollmentsChart" aria-label="Enrollments chart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Recent Enrollments --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">🆕 Recent Enrollments</h2>
            @if($recentEnrollments->isEmpty())
                <p class="text-gray-500 text-sm">No enrollments yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach($recentEnrollments as $enrollment)
                        <li class="flex items-center justify-between gap-2 py-2 border-b border-gray-100 last:border-0">
                            <div class="min-w-0">
                                <span class="font-medium text-gray-800 truncate block">{{ $enrollment->user->name ?? '—' }}</span>
                                <span class="text-sm text-gray-500 truncate block">{{ $enrollment->course->title ?? '—' }}</span>
                            </div>
                            <span class="text-xs text-gray-400 shrink-0">{{ $enrollment->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Recent Reviews --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">📝 Recent Reviews</h2>
            @if($recentReviews->isEmpty())
                <p class="text-gray-500 text-sm">No reviews yet.</p>
            @else
                <ul class="space-y-3">
                    @foreach($recentReviews as $review)
                        <li class="py-2 border-b border-gray-100 last:border-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0 flex-1">
                                    <span class="font-medium text-gray-800">{{ $review->user->name ?? '—' }}</span>
                                    <span class="text-amber-600 text-sm ml-1">★ {{ $review->rating }}/5</span>
                                    <span class="text-sm text-gray-500 block truncate">{{ $review->course->title ?? '—' }}</span>
                                    @if($review->body)
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ Str::limit($review->body, 80) }}</p>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('enrollmentsChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Enrollments',
                        data: @json($chartCounts),
                        backgroundColor: 'rgba(217, 119, 6, 0.6)',
                        borderColor: 'rgb(217, 119, 6)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        });
    </script>
@endsection
