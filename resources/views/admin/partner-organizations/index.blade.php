@extends('layouts.admin')

@section('title', __('Partner Organizations'))
@section('header', __('Partner Organizations'))

@section('content')
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-slate-500 max-w-xl">{{ __('Learners register as a Partner by choosing their organization and entering its registration code. Courses can be restricted to one organization or to all partners.') }}</p>
        @adminCan('partner_organizations', 'create')
            <a href="{{ route('admin.partner-organizations.create') }}" class="admin-btn-accent shrink-0">{{ __('Add organization') }}</a>
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
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Organization') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Registration code') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Learners') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Courses') }}</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">{{ __('Actions') }}</th>
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
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-xs font-medium">{{ __('Active') }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">{{ __('Inactive') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 tabular-nums">{{ $org->users_count }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $org->courses_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('partner_organizations', 'edit')
                                        <form action="{{ route('admin.partner-organizations.regenerate-code', $org) }}" method="post" onsubmit="return confirm({{ Js::from(__('Generate a new code? The current code will stop working for new sign-ups.')) }});">
                                            @csrf
                                            <button type="submit" class="admin-btn-secondary">{{ __('New code') }}</button>
                                        </form>
                                        <a href="{{ route('admin.partner-organizations.edit', $org) }}" class="admin-btn-secondary">{{ __('Edit') }}</a>
                                    @endadminCan
                                    @adminCan('partner_organizations', 'delete')
                                        <form action="{{ route('admin.partner-organizations.destroy', $org) }}" method="post" onsubmit="return confirm({{ Js::from(__('Delete this organization?')) }});">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-danger">{{ __('Delete') }}</button>
                                        </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">{{ __('No partner organizations yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
