<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearnerDashboardController extends Controller
{
    private const AVAILABLE_LIMIT = 12;

    private const BROWSE_LIMIT = 6;

    public function __invoke(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        // 1a. Partners: the courses selected for their own organization (e.g. SFH) get their own section.
        $organization = $user->isPartner() ? $user->partnerOrganization : null;
        $organizationCourses = $organization
            ? Course::published()
                ->where('access_level', Course::ACCESS_PARTNER)
                ->where('partner_organization_id', $organization->id)
                ->with(['category', 'chapters.lessons'])
                ->orderByDesc('id')
                ->get()
            : collect();

        // 1b. Everything else this learner can take: courses made for their category first, then open ones.
        $availableCourses = Course::published()
            ->openTo($user)
            ->whereNotIn('id', $organizationCourses->pluck('id'))
            ->with(['category', 'chapters.lessons'])
            ->orderByRaw("CASE WHEN access_level = 'all' THEN 1 ELSE 0 END")
            ->orderByDesc('id')
            ->take(self::AVAILABLE_LIMIT)
            ->get();
        $shownIds = $availableCourses->pluck('id')->merge($organizationCourses->pluck('id'));

        // 2. Anything else they are enrolled in (e.g. a course whose audience changed later).
        $otherEnrollments = $user->enrollments()
            ->whereNotIn('course_id', $shownIds)
            ->with(['course.category', 'course.chapters.lessons'])
            ->latest()
            ->take(6)
            ->get();
        $courseEnrollments = $user->enrollments()
            ->with(['course.category', 'course.chapters.lessons'])
            ->latest()
            ->take(6)
            ->get();

        // 3. Courses meant for other groups: visible, but not available to this learner.
        $otherGroupCourses = Course::published()
            ->whereNot(fn ($q) => $q->openTo($user))
            ->whereNotIn('id', $enrolledCourseIds)
            ->with(['category', 'chapters.lessons'])
            ->latest()
            ->take(self::BROWSE_LIMIT)
            ->get();

        $bundleEnrollments = $user->bundleEnrollments()
            ->with('bundle')
            ->latest('enrolled_at')
            ->take(5)
            ->get();

        return view('dashboard', [
            'organization' => $organization,
            'organizationCourses' => $organizationCourses,
            'availableCourses' => $availableCourses,
            'enrolledCourseIds' => $enrolledCourseIds,
            'courseEnrollments' => $courseEnrollments,
            'otherEnrollments' => $otherEnrollments,
            'otherGroupCourses' => $otherGroupCourses,
            'bundleEnrollments' => $bundleEnrollments,
        ]);
    }
}
