@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'Users')

@section('content')
    <div class="mb-6">
        <form action="{{ route('admin.users.index') }}" method="get" class="flex gap-2 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="flex-1 rounded-lg border-gray-300 text-sm">
            <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg font-medium hover:bg-gray-300">Search</button>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Full name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Enrollments</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last sign in</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($users as $u)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $u->name }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $u->email }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $u->created_at->format('M j, Y') }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $u->enrollments_count }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $u->last_login_at ? $u->last_login_at->format('M j, Y g:i A') : '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.users.show', $u) }}" class="text-amber-600 hover:underline">Progress</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
