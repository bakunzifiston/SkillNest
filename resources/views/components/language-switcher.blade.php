@props([
    // light = for white backgrounds, dark = for the navy admin sidebar / login panel
    'variant' => 'light',
])

@php
    $current = app()->getLocale();
    $locales = config('localization.locales');
@endphp

@once
    <style>
        .lang-switch { display: inline-flex; align-items: center; margin: 0; }
        .lang-switch-group { display: inline-flex; padding: 2px; border-radius: 999px; border: 1px solid #CBD5E1; background: #fff; }
        .lang-switch button {
            border: 0; background: transparent; cursor: pointer; line-height: 1;
            padding: .38rem .7rem; border-radius: 999px; font-size: .75rem; font-weight: 700; letter-spacing: .04em;
            color: #475569; transition: background-color .15s ease, color .15s ease;
        }
        .lang-switch button:hover { color: #19499B; background: #E8EEF7; }
        .lang-switch button[aria-current="true"] { background: #19499B; color: #fff; }
        .lang-switch button:focus-visible { outline: 2px solid #F16029; outline-offset: 1px; }
        .lang-switch--dark .lang-switch-group { background: rgba(255, 255, 255, .08); border-color: rgba(255, 255, 255, .28); }
        .lang-switch--dark button { color: rgba(255, 255, 255, .85); }
        .lang-switch--dark button:hover { color: #fff; background: rgba(255, 255, 255, .16); }
        .lang-switch--dark button[aria-current="true"] { background: #F16029; color: #fff; }
    </style>
@endonce

<form method="POST" action="{{ route('locale.update') }}" {{ $attributes->class(['lang-switch', 'lang-switch--dark' => $variant === 'dark']) }}>
    @csrf
    <div class="lang-switch-group" role="group" aria-label="{{ __('Language') }}">
        @foreach($locales as $code => $meta)
            <button
                type="submit"
                name="locale"
                value="{{ $code }}"
                lang="{{ $code }}"
                title="{{ $meta['native'] }}"
                aria-label="{{ $meta['native'] }}"
                @if($code === $current) aria-current="true" @endif
            >{{ $meta['short'] }}</button>
        @endforeach
    </div>
</form>
