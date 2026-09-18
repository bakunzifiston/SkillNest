@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'Users')

@section('content')
    <section class="mb-5" aria-labelledby="users-kpi-heading">
        <h2 id="users-kpi-heading" class="sr-only">Users overview</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @foreach($kpis as $kpi)
                @php
                    $styles = match ($kpi['tone'] ?? 'primary') {
                        'accent' => [
                            'card' => 'bg-gradient-to-br from-accent-light to-white border-accent-muted/70',
                            'icon' => 'bg-accent text-white',
                            'value' => 'text-accent-darker',
                            'label' => 'text-accent-dark',
                        ],
                        'success' => [
                            'card' => 'bg-gradient-to-br from-success-light to-white border-success-muted/70',
                            'icon' => 'bg-success text-white',
                            'value' => 'text-success-darker',
                            'label' => 'text-success-dark',
                        ],
                        'slate' => [
                            'card' => 'bg-gradient-to-br from-slate-100 to-white border-slate-200',
                            'icon' => 'bg-navy text-white',
                            'value' => 'text-navy',
                            'label' => 'text-slate-600',
                        ],
                        default => [
                            'card' => 'bg-gradient-to-br from-primary-light to-white border-primary-muted/70',
                            'icon' => 'bg-primary text-white',
                            'value' => 'text-primary-darker',
                            'label' => 'text-primary',
                        ],
                    };
                @endphp
                <div class="rounded-2xl border px-4 py-4 {{ $styles['card'] }}">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $styles['icon'] }}">
                            @include('admin.partials.dashboard-icon', ['icon' => $kpi['icon'], 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold uppercase tracking-wider {{ $styles['label'] }}">{{ $kpi['label'] }}</p>
                            <p class="mt-1 font-display font-bold text-2xl tabular-nums leading-none {{ $styles['value'] }}">
                                {{ number_format($kpi['value']) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="mb-4 flex flex-col xl:flex-row xl:items-center xl:justify-between gap-3">
        <form action="{{ route('admin.users.index') }}" method="get" class="flex flex-wrap gap-2 w-full">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by name or email..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <select name="role" class="rounded-xl border-slate-300 text-sm min-w-[9rem]">
                <option value="all" @selected(($roleFilter ?? 'all') === 'all')>All roles</option>
                <option value="student" @selected(($roleFilter ?? 'all') === 'student')>Students</option>
                <option value="staff" @selected(($roleFilter ?? 'all') === 'staff')>Staff</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected((string) ($roleFilter ?? '') === (string) $role->id)>{{ $role->name }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-xl border-slate-300 text-sm min-w-[8rem]">
                <option value="all" @selected(($statusFilter ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($statusFilter ?? 'all') === 'active')>Active</option>
                <option value="inactive" @selected(($statusFilter ?? 'all') === 'inactive')>Inactive</option>
            </select>
            <button type="submit" class="admin-btn-secondary">Filter</button>
            @if(!empty($search) || ($roleFilter ?? 'all') !== 'all' || ($statusFilter ?? 'all') !== 'all')
                <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @adminCan('users', 'create')
            <a href="{{ route('admin.users.create') }}" class="admin-btn-accent shrink-0">Add user</a>
        @endadminCan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">User</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Role</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Joined</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Enrollments</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider whitespace-nowrap">Last sign in</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $u->isStaff() ? 'bg-navy text-white' : 'bg-primary-light text-primary' }} text-sm font-semibold uppercase">
                                        {{ \Illuminate\Support\Str::substr($u->displayFirstName() ?: $u->email, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-medium text-navy truncate">
                                            {{ trim(($u->displayFirstName() ?: '').' '.($u->displayLastName() ?: '')) ?: '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">
                                <span class="block truncate max-w-[16rem]" title="{{ $u->email }}">{{ $u->email }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($u->isSuperAdmin())
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-navy text-white text-xs font-medium">Super Admin</span>
                                @elseif($u->role)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium">{{ $u->role->name }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Student</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($u->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-xs font-medium">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $u->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($u->enrollments_count > 0)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-xs font-medium tabular-nums">
                                        {{ $u->enrollments_count }}
                                    </span>
                                @else
                                    <span class="text-slate-300">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                {{ $u->last_login_at ? $u->last_login_at->format('M j, Y') : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.users.show', $u) }}" class="admin-btn-secondary">View</a>
                                    @adminCan('users', 'edit')
                                        <a href="{{ route('admin.users.edit', $u) }}" class="admin-btn-secondary">Edit</a>
                                        <form action="{{ route('admin.users.status', $u) }}" method="post">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="admin-btn-secondary">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                    @endadminCan
                                    @adminCan('users', 'delete')
                                        @unless($u->is(auth()->user()))
                                            <form action="{{ route('admin.users.destroy', $u) }}" method="post" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn-danger">Delete</button>
                                            </form>
                                        @endunless
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">
                                @if(!empty($search) || ($roleFilter ?? 'all') !== 'all' || ($statusFilter ?? 'all') !== 'all')
                                    No users match your filters.
                                @else
                                    No users found.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
