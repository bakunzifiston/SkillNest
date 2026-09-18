@extends('layouts.admin')

@section('title', 'Settings')
@section('header', 'Settings')

@section('content')
    <section class="mb-5" aria-labelledby="settings-kpi-heading">
        <h2 id="settings-kpi-heading" class="sr-only">Settings overview</h2>
        <div class="grid grid-cols-2 gap-3 max-w-xl">
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
                                {{ $kpi['display'] ?? number_format($kpi['value']) }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    @if(session('success'))
        <div class="mb-4 max-w-2xl p-3 rounded-xl bg-success-light text-success-darker border border-success-muted text-sm">{{ session('success') }}</div>
    @endif

    <div class="max-w-2xl">
        <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="font-display font-semibold text-sm text-navy">Platform logo</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Shown in the header and footer. Prefer a transparent PNG or SVG.</p>
                </div>
                <div class="p-4 space-y-4">
                    @if($logoUrl)
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <img src="{{ $logoUrl }}" alt="Site logo" class="object-contain object-left" style="height: 48px; max-width: 280px;">
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-slate-300 text-primary focus:ring-primary">
                                Remove logo
                            </label>
                        </div>
                    @else
                        <p class="text-sm text-slate-500">No logo uploaded — the site name is shown instead.</p>
                    @endif

                    <div>
                        <label for="logo" class="block text-sm font-medium text-navy mb-1.5">Upload logo</label>
                        <input
                            type="file"
                            name="logo"
                            id="logo"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-accent file:text-white file:font-medium hover:file:bg-accent-dark"
                        >
                        <p class="mt-1.5 text-xs text-slate-400">JPEG, PNG, GIF, WebP or SVG. Max 2MB.</p>
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @adminCan('settings', 'edit')
                    <button type="submit" class="admin-btn-accent">Save settings</button>
                @endadminCan
                @adminCan('partners', 'view')
                    <a href="{{ route('admin.partners.index') }}" class="admin-btn-secondary">Manage partner logos</a>
                @endadminCan
            </div>
        </form>
    </div>
@endsection
