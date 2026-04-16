<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Course $course): View|RedirectResponse
    {
        $course->load(['chapters.lessons', 'category', 'instructor', 'quizzes' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')->withCount('questions')]);
        $upcomingLiveSessions = $course->liveSessions()
            ->with('invitedAttendees')
            ->orderBy('scheduled_at')
            ->get()
            ->filter(fn ($s) => $s->scheduled_at->copy()->addMinutes($s->duration_minutes)->isFuture());
        $enrolled = auth()->check() && auth()->user()->hasEnrolled($course);
        $completedCount = $enrolled ? auth()->user()->completedLessonsCountForCourse($course) : 0;
        $totalLessons = $course->chapters->sum(fn ($ch) => $ch->lessons->count());

        $resumeLesson = null;
        $completedLessonIds = collect();
        if ($enrolled && $totalLessons > 0) {
            $completedLessonIds = auth()->user()->lessonCompletions()->pluck('lesson_id');
            foreach ($course->chapters as $chapter) {
                foreach ($chapter->lessons as $lesson) {
                    if (! $completedLessonIds->contains($lesson->id)) {
                        $resumeLesson = $lesson;
                        break 2;
                    }
                }
            }
        }

        return view('courses.show', compact('course', 'enrolled', 'completedCount', 'totalLessons', 'resumeLesson', 'completedLessonIds', 'upcomingLiveSessions'));
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('courses.show', $course, false));
            return redirect()->route('login');
        }

        $user = auth()->user();
        if ($user->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course)->with('info', 'You are already enrolled.');
        }

        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id]);
        $course->increment('students_count');

        return redirect()->route('courses.show', $course)->with('success', 'You are enrolled! You can start the course below.');
    }

    public function myCourses(): View
    {
        $enrollments = auth()->user()
            ->enrollments()
            ->with(['course.chapters.lessons'])
            ->latest()
            ->paginate(12);

        return view('courses.my-courses', compact('enrollments'));
    }

    public function showLesson(Course $course, Lesson $lesson): View|RedirectResponse
    {
        if (! auth()->check()) {
            session()->put('url.intended', route('courses.lessons.show', [$course, $lesson], false));
            return redirect()->route('login');
        }
        if (! auth()->user()->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course)->with('error', 'Please enroll in this course first.');
        }
        if ($lesson->chapter->course_id !== $course->id) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', 'That lesson link is no longer valid for this course.');
        }

        $course->load(['chapters.lessons']);
        $lesson->load('chapter');
        $completedLessonIds = auth()->user()->lessonCompletions()->pluck('lesson_id');
        $completed = $completedLessonIds->contains($lesson->id);

        return view('courses.lesson', compact('course', 'lesson', 'completed', 'completedLessonIds'));
    }

    public function completeLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $course = $lesson->chapter->course;
        if (! auth()->user()->hasEnrolled($course)) {
            return redirect()->route('courses.show', $course);
        }

        LessonCompletion::firstOrCreate(
            ['user_id' => auth()->id(), 'lesson_id' => $lesson->id],
            ['completed_at' => now()]
        );

        // Refresh bundle progress for any bundle that includes this course
        $course->load('bundles');
        foreach ($course->bundles as $bundle) {
            $be = auth()->user()->bundleEnrollments()->where('bundle_id', $bundle->id)->first();
            if ($be) {
                $be->refreshProgress();
            }
        }

        $nextLesson = $lesson->chapter->lessons->where('sort_order', '>', $lesson->sort_order)->first()
            ?? $lesson->chapter->course->chapters->where('sort_order', '>', $lesson->chapter->sort_order)->first()?->lessons->first();

        if ($nextLesson) {
            return redirect()->route('courses.lessons.show', [$course, $nextLesson])->with('success', 'Lesson marked complete.');
        }

        return redirect()->route('courses.show', $course)->with('success', 'Lesson marked complete. You have finished this course!');
    }
}
