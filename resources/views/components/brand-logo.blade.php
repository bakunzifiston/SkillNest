@props([
    'variant' => 'light',
    'showName' => true,
    'size' => 'md',
])

@php
    $heights = [
        'sm' => 'h-10',
        'md' => 'h-12',
        'lg' => 'h-14',
        'xl' => 'h-20',
        '2xl' => 'h-24',
    ];
    $maxWidths = [
        'sm' => '160px',
        'md' => '200px',
        'lg' => '240px',
        'xl' => '300px',
        '2xl' => '340px',
    ];
    $nameSizes = [
        'sm' => 'text-lg',
        'md' => 'text-xl',
        'lg' => 'text-2xl',
        'xl' => 'text-3xl',
        '2xl' => 'text-4xl',
    ];
    $usingCustomLogo = ! empty($siteLogoUrl);
    $src = $usingCustomLogo ? $siteLogoUrl : asset('images/brand/logo-mark.svg');
    $nameColor = $variant === 'dark' ? 'text-white' : 'text-navy';
    $imgClass = ($heights[$size] ?? $heights['md']).' w-auto object-contain object-left shrink-0';
@endphp

<span {{ $attributes->class('inline-flex items-center gap-2.5 min-w-0') }}>
    <img
        src="{{ $src }}"
        alt="{{ config('app.name') }}"
        class="{{ $imgClass }}"
        style="max-width: {{ $maxWidths[$size] ?? '168px' }};"
    >
    @if($showName && ! $usingCustomLogo)
        <span class="font-display font-bold tracking-tight {{ $nameColor }} {{ $nameSizes[$size] ?? 'text-lg' }} truncate">{{ config('app.name') }}</span>
    @endif
</span>
