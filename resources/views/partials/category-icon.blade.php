@php
    $class = $class ?? 'h-5 w-5';
    $key = strtolower((string) ($slug ?? $name ?? ''));

    $kind = match (true) {
        str_contains($key, 'develop') || str_contains($key, 'tech') || str_contains($key, 'code') || str_contains($key, 'program') => 'code',
        str_contains($key, 'design') || str_contains($key, 'creat') => 'design',
        str_contains($key, 'market') || str_contains($key, 'commun') => 'megaphone',
        str_contains($key, 'business') || str_contains($key, 'entrepr') || str_contains($key, 'finance') => 'briefcase',
        str_contains($key, 'health') || str_contains($key, 'medic') => 'heart',
        str_contains($key, 'digital') || str_contains($key, 'data') => 'chip',
        default => 'book',
    };
@endphp

@if($kind === 'code')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 9l-4 3 4 3m8-6l4 3-4 3M13 5l-2 14"/></svg>
@elseif($kind === 'design')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5L12 3zM5 19h14"/></svg>
@elseif($kind === 'megaphone')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.5L18 3v14l-7-2.5V5.5zM11 15l-3 4H6v-5.5M4 10h2"/></svg>
@elseif($kind === 'briefcase')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 6V5a2 2 0 012-2h2a2 2 0 012 2v1m-9 0h12a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2z"/></svg>
@elseif($kind === 'heart')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.3 12.3C2.6 10.6 2.6 7.9 4.3 6.2c1.7-1.7 4.4-1.7 6.1 0L12 7.8l1.6-1.6c1.7-1.7 4.4-1.7 6.1 0 1.7 1.7 1.7 4.4 0 6.1L12 20l-7.7-7.7z"/></svg>
@elseif($kind === 'chip')
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M3 9h2m-2 6h2m14-6h2m-2 6h2M7 7h10v10H7V7z"/></svg>
@else
    <svg class="{{ $class }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5V6.5A2.5 2.5 0 016.5 4H20v13H6.5A2.5 2.5 0 004 19.5z"/></svg>
@endif
