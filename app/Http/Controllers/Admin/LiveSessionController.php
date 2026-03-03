<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LiveSessionInvitation;
use App\Models\Course;
use App\Models\LiveSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class LiveSessionController extends Controller
{
    public function index(Request $request): View
    {
        $query = LiveSession::with('course')->withCount('invitedAttendees');
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        $liveSessions = $query->orderBy('scheduled_at')->paginate(15);
        $courses = Course::orderBy('title')->get();
        return view('admin.live-sessions.index', compact('liveSessions', 'courses'));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->old('course_id', $request->get('course_id'));
        return view('admin.live-sessions.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url' => 'required|url|max:500',
            'meeting_password' => 'nullable|string|max:100',
            'invited_user_ids' => 'nullable|array',
            'invited_user_ids.*' => 'exists:users,id',
        ]);
        $invitedUserIds = $request->input('invited_user_ids', []);
        if (is_string($invitedUserIds)) {
            $invitedUserIds = array_filter(array_map('intval', explode(',', $invitedUserIds)));
        }
        $course = Course::findOrFail($validated['course_id']);
        $enrolledUserIds = $course->enrollments()->pluck('user_id')->toArray();
        $invitedUserIds = array_values(array_intersect($invitedUserIds, $enrolledUserIds));

        unset($validated['invited_user_ids']);
        $liveSession = LiveSession::create($validated);
        $liveSession->invitedAttendees()->sync($invitedUserIds);

        foreach ($liveSession->invitedAttendees as $user) {
            Mail::to($user->email)->send(new LiveSessionInvitation($liveSession, $user));
        }

        return redirect()->route('admin.live-sessions.index')->with('success', 'Live session created. Invitation emails sent to ' . count($invitedUserIds) . ' attendee(s).');
    }

    public function edit(LiveSession $liveSession): View
    {
        $liveSession->load(['course', 'invitedAttendees']);
        $enrolledUsers = $liveSession->course->enrollments()->with('user')->get()->pluck('user')->filter();
        return view('admin.live-sessions.edit', compact('liveSession', 'enrolledUsers'));
    }

    public function update(Request $request, LiveSession $liveSession): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url' => 'required|url|max:500',
            'meeting_password' => 'nullable|string|max:100',
            'invited_user_ids' => 'nullable|array',
            'invited_user_ids.*' => 'exists:users,id',
        ]);
        $invitedUserIds = $request->input('invited_user_ids', []);
        if (is_string($invitedUserIds)) {
            $invitedUserIds = array_filter(array_map('intval', explode(',', $invitedUserIds)));
        }
        $enrolledUserIds = $liveSession->course->enrollments()->pluck('user_id')->toArray();
        $invitedUserIds = array_values(array_intersect($invitedUserIds, $enrolledUserIds));

        unset($validated['invited_user_ids']);
        $liveSession->update($validated);
        $previousIds = $liveSession->invitedAttendees->pluck('id')->toArray();
        $liveSession->invitedAttendees()->sync($invitedUserIds);

        $newlyAdded = array_diff($invitedUserIds, $previousIds);
        foreach ($liveSession->invitedAttendees()->whereIn('users.id', $newlyAdded)->get() as $user) {
            Mail::to($user->email)->send(new LiveSessionInvitation($liveSession, $user));
        }

        $message = count($newlyAdded) > 0
            ? 'Live session updated. Invitation emails sent to ' . count($newlyAdded) . ' new attendee(s).'
            : 'Live session updated.';
        return redirect()->route('admin.live-sessions.index')->with('success', $message);
    }

    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        $liveSession->delete();
        return redirect()->route('admin.live-sessions.index')->with('success', 'Live session deleted.');
    }
}
