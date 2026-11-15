@extends('layouts.admin')

@section('title', 'Partner Organizations')
@section('header', 'Partner Organizations')

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-slate-500 max-w-xl">Learners register as a Partner by choosing their organization and entering its registration code. Courses can be restricted to one organization or to all partners.</p>
        @adminCan('partner_organizations', 'create')
            <a href="{{ route('admin.partner-organizations.create') }}" class="admin-btn-accent shrink-0">Add organization</a>
        @endadminCan
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Organization</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Registration code</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Learners</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($organizations as $org)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $org->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-mono tracking-widest select-all">{{ $org->registration_code ?: '—' }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($org->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums">{{ $org->users_count }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $org->courses_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('partner_organizations', 'edit')
                                        <form action="{{ route('admin.partner-organizations.regenerate-code', $org) }}" method="post" onsubmit="return confirm('Generate a new code? The current code will stop working for new sign-ups.');">
                                            @csrf
                                            <button type="submit" class="admin-btn-secondary">New code</button>
                                        </form>
                                        <a href="{{ route('admin.partner-organizations.edit', $org) }}" class="admin-btn-secondary">Edit</a>
                                    @endadminCan
                                    @adminCan('partner_organizations', 'delete')
                                        <form action="{{ route('admin.partner-organizations.destroy', $org) }}" method="post" onsubmit="return confirm('Delete this organization?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-danger">Delete</button>
                                        </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No partner organizations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
