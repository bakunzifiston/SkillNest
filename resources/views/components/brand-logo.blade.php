@props([
    'variant' => 'light',
    'showName' => true,
    'size' => 'md',
])

@php
    $heights = [
        'sm' => 'h-8',
        'md' => 'h-9',
        'lg' => 'h-11',
        'xl' => 'h-14',
        '2xl' => 'h-16',
    ];
    $maxWidths = [
        'sm' => '128px',
        'md' => '150px',
        'lg' => '190px',
        'xl' => '230px',
        '2xl' => '260px',
    ];
    $nameSizes = [
        'sm' => '11px',
        'md' => '12px',
        'lg' => '14px',
        'xl' => '17px',
        '2xl' => '20px',
    ];
    $usingCustomLogo = ! empty($siteLogoUrl);
    $src = $usingCustomLogo ? $siteLogoUrl : asset('images/brand/logo-mark.png');
    $nameColor = $variant === 'dark' ? 'text-white' : 'text-navy';
    $imgClass = ($heights[$size] ?? $heights['md']).' w-auto object-contain object-left shrink-0';
@endphp

<span {{ $attributes->class('inline-flex flex-col items-start gap-0.5 min-w-0') }}>
    <img
        src="{{ $src }}"
        alt="{{ config('app.name') }}"
        class="{{ $imgClass }}"
        style="max-width: {{ $maxWidths[$size] ?? '168px' }};"
    >
    @if($showName)
        <span class="font-display font-semibold leading-none {{ $nameColor }} truncate" style="font-size: {{ $nameSizes[$size] ?? '14px' }}; letter-spacing: 0.1em;">{{ config('app.name') }}</span>
    @endif
</span>
