@extends('layouts.admin')

@section('title', 'Progress — ' . $user->name)
@section('header', 'Student progress: ' . $user->name)

@section('content')
    <div class="mb-8 p-6 bg-white rounded-xl border border-gray-200">
        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Full name</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $user->name }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Email</dt>
                <dd class="mt-1 text-gray-700">{{ $user->email }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Account created</dt>
                <dd class="mt-1 text-gray-700">{{ $user->created_at->format('M j, Y') }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500 uppercase">Last sign in</dt>
                <dd class="mt-1 text-gray-700">{{ $user->last_login_at ? $user->last_login_at->format('M j, Y g:i A') : '—' }}</dd>
            </div>
        </dl>
        <div class="mt-4">
            <a href="{{ route('admin.users.index') }}" class="text-sm text-amber-600 hover:underline">← Back to users</a>
        </div>
    </div>

    <h2 class="text-lg font-semibold text-gray-800 mb-4">All courses — progress</h2>
    <p class="text-sm text-gray-500 mb-4">Every course on the platform and this user's progress in each.</p>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($courseProgress as $row)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $row->course->title }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $row->course->category->name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @if($row->enrolled)
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">Enrolled</span>
                        @else
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Not enrolled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($row->enrolled)
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 rounded-full bg-gray-200 overflow-hidden">
                                    <div class="h-full rounded-full bg-amber-500" style="width: {{ $row->percent }}%"></div>
                                </div>
                                <span class="text-sm text-gray-600">{{ $row->completed }} / {{ $row->total_lessons }} ({{ $row->percent }}%)</span>
                            </div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No courses on the platform yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
