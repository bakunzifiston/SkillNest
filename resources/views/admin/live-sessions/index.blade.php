@extends('layouts.admin')

@section('title', 'Live Sessions')
@section('header', 'Live Sessions')

@section('content')
    <div class="mb-6 flex flex-wrap justify-between items-center gap-4">
        <p class="text-gray-600">Schedule live sessions (Zoom, Meet, etc.) and add the join link. Students see upcoming sessions on the course page.</p>
        <a href="{{ route('admin.live-sessions.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add live session</a>
    </div>
    <div class="mb-4">
        <form method="get" class="flex gap-2 items-center">
            <select name="course_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                <option value="">All courses</option>
                @foreach($courses as $c)
                    <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheduled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invitees</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($liveSessions as $session)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $session->title }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $session->course->title ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $session->scheduled_at->format('M j, Y H:i') }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $session->duration_minutes }} min</td>
                    <td class="px-6 py-4 text-gray-600">{{ $session->invited_attendees_count ?? 0 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.live-sessions.edit', $session) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.live-sessions.destroy', $session) }}" method="post" class="inline" onsubmit="return confirm('Delete this live session?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No live sessions yet. <a href="{{ route('admin.live-sessions.create') }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($liveSessions->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $liveSessions->links() }}</div>
        @endif
    </div>
@endsection
