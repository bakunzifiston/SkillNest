@extends('layouts.admin')

@section('title', 'Partner logos')
@section('header', 'Partner logos')

@section('content')
    <section class="mb-5" aria-labelledby="partners-kpi-heading">
        <h2 id="partners-kpi-heading" class="sr-only">Partners overview</h2>
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
        <form action="{{ route('admin.partners.index') }}" method="get" class="flex flex-wrap gap-2 w-full sm:max-w-md">
            <input
                type="text"
                name="search"
                value="{{ $search ?? '' }}"
                placeholder="Search by name..."
                class="flex-1 rounded-xl border-slate-300 text-sm min-w-[12rem]"
            >
            <button type="submit" class="admin-btn-secondary">Search</button>
            @if(!empty($search))
                <a href="{{ route('admin.partners.index') }}" class="admin-btn-secondary">Clear</a>
            @endif
        </form>
        @adminCan('partners', 'create')
            <a href="{{ route('admin.partners.create') }}" class="admin-btn-accent shrink-0">Add partner logo</a>
        @endadminCan
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">#</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Logo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($partners as $i => $partner)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 text-slate-400 tabular-nums">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="inline-flex items-center justify-center h-14 w-36 rounded-xl border border-slate-200 bg-slate-50 px-3">
                                    @if($partner->logo_url)
                                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name ?? 'Partner logo' }}" class="max-h-10 max-w-full object-contain">
                                    @else
                                        <span class="text-xs text-slate-400">No image</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($partner->name)
                                    <span class="font-medium text-navy">{{ $partner->name }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">Unnamed</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    @adminCan('partners', 'edit')
                                        <a href="{{ route('admin.partners.edit', $partner) }}" class="admin-btn-secondary">Edit</a>
                                    @endadminCan
                                    @adminCan('partners', 'delete')
                                        <form action="{{ route('admin.partners.destroy', $partner) }}" method="post" onsubmit="return confirm('Remove this partner?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="admin-btn-danger">Delete</button>
                                        </form>
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center">
                                @if(!empty($search))
                                    <p class="text-sm text-slate-500">No partners match “{{ $search }}”.</p>
                                @else
                                    <p class="text-sm text-slate-500 mb-3">No partners yet.</p>
                                    @adminCan('partners', 'create')
                                        <a href="{{ route('admin.partners.create') }}" class="admin-btn-accent">Add partner logo</a>
                                    @endadminCan
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
