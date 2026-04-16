<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\BundleEnrollment;
use App\Models\Enrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BundleController extends Controller
{
    public function index(): View
    {
        $bundles = Bundle::where('status', Bundle::STATUS_PUBLISHED)
            ->withCount('courses')
            ->latest()
            ->paginate(12);
        return view('bundles.index', compact('bundles'));
    }

    public function show(Bundle $bundle): View|RedirectResponse
    {
        if ($bundle->status !== Bundle::STATUS_PUBLISHED) {
            abort(404);
        }
        $bundle->load('courses.category');
        $enrolled = auth()->check() && auth()->user()->hasEnrolledBundle($bundle);
        $bundleEnrollment = null;
        if ($enrolled) {
            $bundleEnrollment = auth()->user()->bundleEnrollments()->where('bundle_id', $bundle->id)->first();
            $bundleEnrollment?->refreshProgress();
        }
        return view('bundles.show', compact('bundle', 'enrolled', 'bundleEnrollment'));
    }

    public function enroll(Request $request, Bundle $bundle): RedirectResponse
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('bundles.show', $bundle, false));
            return redirect()->route('login');
        }
        if ($bundle->status !== Bundle::STATUS_PUBLISHED) {
            abort(404);
        }
        $user = auth()->user();
        if ($user->hasEnrolledBundle($bundle)) {
            return redirect()->route('bundles.show', $bundle)->with('info', 'You are already enrolled in this bundle.');
        }

        $bundleEnrollment = BundleEnrollment::create([
            'user_id' => $user->id,
            'bundle_id' => $bundle->id,
            'enrolled_at' => now(),
            'total_courses' => $bundle->courses()->count(),
        ]);

        foreach ($bundle->courses as $course) {
            if (! $user->hasEnrolled($course)) {
                Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
                $course->increment('students_count');
            }
        }

        $bundleEnrollment->refreshProgress();

        return redirect()->route('bundles.show', $bundle)->with('success', 'You are enrolled! You now have access to all courses in this bundle.');
    }

    public function myBundles(): View
    {
        $enrollments = auth()->user()
            ->bundleEnrollments()
            ->with('bundle.courses')
            ->latest('enrolled_at')
            ->paginate(12);
        return view('bundles.my-bundles', compact('enrollments'));
    }
}
