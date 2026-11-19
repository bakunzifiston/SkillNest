<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel')) — {{ config('app.name') }}</title>

        <x-brand-meta />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased min-h-screen bg-slate-50">
        <div class="min-h-screen grid lg:grid-cols-2">
            <aside class="auth-aside relative hidden lg:flex flex-col justify-between overflow-hidden text-white p-10 xl:p-14" style="background: linear-gradient(170deg, #19499B 0%, #153F86 45%, #102C5E 100%);">
                <div class="absolute inset-0 opacity-[0.12]" aria-hidden="true" style="background-image: radial-gradient(circle at 20% 20%, #F16029 0, transparent 42%), radial-gradient(circle at 80% 80%, #19499B 0, transparent 40%);"></div>
                <div class="relative flex items-start justify-between gap-4">
                    {{-- White pill, like the website menu bar, so the blue part of the logo stays visible on the blue panel --}}
                    <a href="{{ route('home') }}" class="inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-white" style="background: #fff; border-radius: 999px; padding: .5rem 1.25rem; box-shadow: 0 12px 28px -14px rgba(11, 27, 51, .6);">
                        <x-brand-logo size="md" />
                    </a>
                    <x-language-switcher variant="dark" />
                </div>
                <div class="relative flex-1 flex items-center justify-center" style="padding: 1.5rem 0; min-height: 0;">
                    <div style="width: 100%; max-width: 26rem;">
                        @include('partials.auth-illustration')
                    </div>
                </div>
                <div class="relative max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-accent">Digital Jobs for Youth in Health</p>
                    <h1 class="mt-4 font-display font-bold text-3xl xl:text-4xl tracking-tight leading-tight">
                        {{ __('Learn practical skills as a Digital Community Champion') }}
                    </h1>
                    <p class="mt-4 text-slate-300 leading-relaxed">
                        {{ __('Access courses, track your progress, and grow your impact through the KoraLink Agents Academy.') }}
                    </p>
                </div>
                <p class="relative text-sm text-slate-400" style="margin-top: 1.5rem;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </aside>

            <div class="flex flex-col justify-center px-4 py-10 sm:px-8 lg:px-12 xl:px-16">
                <div class="lg:hidden mb-8 flex flex-col items-center gap-4">
                    <a href="{{ route('home') }}" class="inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg">
                        <x-brand-logo size="lg" />
                    </a>
                    <x-language-switcher />
                </div>

                <div class="w-full max-w-md mx-auto">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-sm text-slate-500">
                        <a href="{{ route('home') }}" class="font-medium text-primary hover:text-accent-dark transition">{{ __('← Back to home') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
