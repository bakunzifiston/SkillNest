<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Partner;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = $request->get('chart_period', 'monthly'); // weekly | monthly

        $chartData = $this->enrollmentChartData($period);
        $recentEnrollments = Enrollment::with(['user', 'course'])
            ->latest()
            ->take(15)
            ->get();
        $recentReviews = Review::with(['user', 'course'])
            ->latest()
            ->take(15)
            ->get();

        return view('admin.dashboard', [
            'chartLabels' => $chartData['labels'],
            'chartCounts' => $chartData['counts'],
            'chartPeriod' => $period,
            'recentEnrollments' => $recentEnrollments,
            'recentReviews' => $recentReviews,
            'stats' => [
                'categories' => Category::count(),
                'courses' => \App\Models\Course::count(),
                'bundles' => Bundle::count(),
                'users' => User::count(),
                'partners' => Partner::count(),
            ],
        ]);
    }

    private function enrollmentChartData(string $period): array
    {
        $now = Carbon::now();
        if ($period === 'weekly') {
            $start = $now->copy()->subWeeks(11)->startOfWeek();
            $raw = Enrollment::query()
                ->select(DB::raw("CONCAT(YEAR(created_at), '-', LPAD(WEEK(created_at, 3), 2, '0')) as period"), DB::raw('count(*) as total'))
                ->where('created_at', '>=', $start)
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');
            $labels = [];
            $counts = [];
            for ($i = 11; $i >= 0; $i--) {
                $week = $now->copy()->subWeeks($i);
                $key = $week->format('o') . '-' . str_pad((string) $week->weekOfYear, 2, '0', STR_PAD_LEFT);
                $labels[] = 'W' . $week->weekOfYear . ' ' . $week->format('M');
                $counts[] = $raw->get($key, 0);
            }
        } else {
            $start = $now->copy()->subMonths(11)->startOfMonth();
            $raw = Enrollment::query()
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"), DB::raw('count(*) as total'))
                ->where('created_at', '>=', $start)
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');
            $labels = [];
            $counts = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = $now->copy()->subMonths($i);
                $key = $month->format('Y-m');
                $labels[] = $month->format('M Y');
                $counts[] = $raw->get($key, 0);
            }
        }

        return ['labels' => $labels, 'counts' => $counts];
    }
}
