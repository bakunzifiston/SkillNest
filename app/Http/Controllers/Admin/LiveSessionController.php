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
        $search = trim((string) $request->get('search', ''));
        $courseId = $request->filled('course_id') ? (int) $request->get('course_id') : null;

        $query = LiveSession::with('course')->withCount('invitedAttendees');
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('course', fn ($cq) => $cq->where('title', 'like', "%{$search}%"));
            });
        }

        $liveSessions = $query->orderBy('scheduled_at')->paginate(15)->withQueryString();
        $courses = Course::orderBy('title')->get();

        $now = now();
        $all = LiveSession::withCount('invitedAttendees')->get();
        $totalSessions = $all->count();
        $upcoming = $all->filter(fn ($s) => $s->scheduled_at->gte($now))->count();
        $past = $totalSessions - $upcoming;
        $totalInvitees = (int) $all->sum('invited_attendees_count');

        $kpis = [
            [
                'label' => 'Sessions',
                'value' => $totalSessions,
                'icon' => 'live',
                'tone' => 'primary',
            ],
            [
                'label' => 'Upcoming',
                'value' => $upcoming,
                'icon' => 'pulse',
                'tone' => 'success',
            ],
            [
                'label' => 'Past',
                'value' => $past,
                'icon' => 'check',
                'tone' => 'slate',
            ],
            [
                'label' => 'Invitees',
                'value' => $totalInvitees,
                'icon' => 'users',
                'tone' => 'accent',
            ],
        ];

        return view('admin.live-sessions.index', compact(
            'liveSessions',
            'courses',
            'kpis',
            'search',
            'courseId'
        ));
    }

    public function create(Request $request): View
    {
        $courses = Course::orderBy('title')->get();
        $selectedCourseId = $request->old('course_id', $request->get('course_id'));
        return view('admin.live-sessions.create', compact('courses', 'selectedCourseId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'invited_user_ids' => $this->normalizeInvitedUserIds($request),
        ]);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url' => 'required|url|max:500',
            'meeting_password' => 'nullable|string|max:100',
            'invited_user_ids' => 'nullable|array',
            'invited_user_ids.*' => 'integer|exists:users,id',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $enrolledUserIds = $course->enrollments()->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $invitedUserIds = array_values(array_intersect($validated['invited_user_ids'] ?? [], $enrolledUserIds));

        unset($validated['invited_user_ids']);
        $liveSession = LiveSession::create($validated);
        $liveSession->invitedAttendees()->sync($invitedUserIds);

        $sent = 0;
        foreach ($liveSession->invitedAttendees as $user) {
            try {
                Mail::to($user->email)->send(new LiveSessionInvitation($liveSession, $user));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $message = $sent > 0
            ? "Live session created. Invitation emails sent to {$sent} attendee(s)."
            : (
                count($invitedUserIds) > 0
                    ? 'Live session created, but invitation emails could not be sent. Check mail settings.'
                    : 'Live session created.'
            );

        return redirect()->route('admin.live-sessions.index')->with('success', $message);
    }

    public function show(LiveSession $liveSession): View
    {
        $liveSession->load(['course.instructor', 'invitedAttendees']);

        $isUpcoming = $liveSession->scheduled_at->isFuture();
        $endsAt = $liveSession->scheduled_at->copy()->addMinutes((int) $liveSession->duration_minutes);
        $isLive = ! $isUpcoming && $endsAt->isFuture();

        $kpis = [
            [
                'label' => 'Invitees',
                'value' => $liveSession->invitedAttendees->count(),
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Duration',
                'value' => $liveSession->duration_minutes,
                'suffix' => ' min',
                'icon' => 'live',
                'tone' => 'accent',
            ],
            [
                'label' => 'Status',
                'display' => $isLive ? 'Live now' : ($isUpcoming ? 'Upcoming' : 'Past'),
                'value' => 0,
                'icon' => $isLive ? 'pulse' : ($isUpcoming ? 'check' : 'folder'),
                'tone' => $isLive ? 'accent' : ($isUpcoming ? 'success' : 'slate'),
            ],
        ];

        return view('admin.live-sessions.show', compact('liveSession', 'kpis', 'isUpcoming', 'isLive', 'endsAt'));
    }

    public function edit(LiveSession $liveSession): View
    {
        $liveSession->load(['course', 'invitedAttendees']);
        $enrolledUsers = $liveSession->course->enrollments()->with('user')->get()->pluck('user')->filter();
        return view('admin.live-sessions.edit', compact('liveSession', 'enrolledUsers'));
    }

    public function update(Request $request, LiveSession $liveSession): RedirectResponse
    {
        $request->merge([
            'invited_user_ids' => $this->normalizeInvitedUserIds($request),
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'meeting_url' => 'required|url|max:500',
            'meeting_password' => 'nullable|string|max:100',
            'invited_user_ids' => 'nullable|array',
            'invited_user_ids.*' => 'integer|exists:users,id',
        ]);

        $enrolledUserIds = $liveSession->course->enrollments()->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $invitedUserIds = array_values(array_intersect($validated['invited_user_ids'] ?? [], $enrolledUserIds));

        unset($validated['invited_user_ids']);
        $liveSession->update($validated);
        $previousIds = $liveSession->invitedAttendees->pluck('id')->map(fn ($id) => (int) $id)->all();
        $liveSession->invitedAttendees()->sync($invitedUserIds);

        $newlyAdded = array_diff($invitedUserIds, $previousIds);
        $sent = 0;
        foreach ($liveSession->invitedAttendees()->whereIn('users.id', $newlyAdded)->get() as $user) {
            try {
                Mail::to($user->email)->send(new LiveSessionInvitation($liveSession, $user));
                $sent++;
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $message = $sent > 0
            ? "Live session updated. Invitation emails sent to {$sent} new attendee(s)."
            : 'Live session updated.';

        return redirect()->route('admin.live-sessions.index')->with('success', $message);
    }

    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        $liveSession->delete();
        return redirect()->route('admin.live-sessions.index')->with('success', 'Live session deleted.');
    }

    /**
     * Form sends invitee IDs as a comma-separated string in a hidden field.
     *
     * @return list<int>
     */
    private function normalizeInvitedUserIds(Request $request): array
    {
        $raw = $request->input('invited_user_ids', []);

        if (is_string($raw)) {
            $raw = trim($raw) === ''
                ? []
                : (preg_split('/\s*,\s*/', $raw) ?: []);
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $raw))));
    }
}
