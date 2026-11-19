@extends('layouts.admin')

@section('title', __('Roles & Permissions'))
@section('header', __('Roles & permissions'))

@section('content')
    <section class="mb-5" aria-labelledby="roles-kpi-heading">
        <h2 id="roles-kpi-heading" class="sr-only">{{ __('Roles overview') }}</h2>
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

    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-slate-500">{{ __('Roles control which admin modules a user can open and whether they can view, create, edit, or delete inside each one.') }}</p>
        @adminCan('roles', 'create')
            <a href="{{ route('admin.roles.create') }}" class="admin-btn-accent shrink-0">{{ __('Add role') }}</a>
        @endadminCan
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Role') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Modules') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Users') }}</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($roles as $role)
                        @php
                            $moduleCount = count($role->permissionMap());
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <p class="font-medium text-navy">{{ $role->name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $role->description ?: ($role->is_system ? __('System role') : __('Custom role')) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-xs font-medium tabular-nums">
                                    {{ trans_choice('{0} :count modules|{1} :count module|[2,*] :count modules', $moduleCount) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="tabular-nums text-slate-600">{{ $role->users_count }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('roles', 'edit')
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="admin-btn-secondary">{{ $role->isSuperAdmin() ? __('View') : __('Edit') }}</a>
                                    @endadminCan
                                    @adminCan('roles', 'delete')
                                        @unless($role->is_system)
                                            <form action="{{ route('admin.roles.destroy', $role) }}" method="post" onsubmit="return confirm({{ Js::from(__('Delete this role? Assigned users will become students until you give them another role.')) }});">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="admin-btn-danger">{{ __('Delete') }}</button>
                                            </form>
                                        @endunless
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No roles yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
