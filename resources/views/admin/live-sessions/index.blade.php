@extends('layouts.admin')

@section('title', 'Live Sessions')
@section('header', 'Live Sessions')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">Schedule live sessions and add the join link. Students see upcoming sessions on the course page.</p>
        <a href="{{ route('admin.live-sessions.create') }}" class="admin-btn-accent">Add live session</a>
    </div>
    <div class="mb-4">
        <form method="get">
            <select name="course_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Session</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Course</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Scheduled</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Invitees</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($liveSessions as $session)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $session->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $session->course->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $session->scheduled_at->format('M j, Y H:i') }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $session->duration_minutes }} min</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $session->invited_attendees_count ?? 0 }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.live-sessions.edit', $session) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.live-sessions.destroy', $session) }}" method="post" onsubmit="return confirm('Delete this live session?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No live sessions yet.</p>
                                <a href="{{ route('admin.live-sessions.create') }}" class="admin-btn-accent">Add live session</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($liveSessions->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $liveSessions->links() }}</div>
        @endif
    </div>
@endsection
