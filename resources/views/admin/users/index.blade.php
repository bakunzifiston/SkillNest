@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'Users')

@section('content')
    <div class="mb-5">
        <form action="{{ route('admin.users.index') }}" method="get" class="flex flex-wrap gap-2 max-w-md">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="flex-1 rounded-xl border-slate-300 text-sm">
            <button type="submit" class="admin-btn-secondary">Search</button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Full name</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Account created</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Enrollments</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Last sign in</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $u->email }}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $u->enrollments_count }}</td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $u->last_login_at ? $u->last_login_at->format('M j, Y g:i A') : '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('admin.users.show', $u) }}" class="admin-btn-secondary">Progress</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $users->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
