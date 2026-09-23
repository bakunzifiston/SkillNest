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
            <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-navy text-white p-10 xl:p-14">
                <div class="absolute inset-0 opacity-[0.12]" aria-hidden="true" style="background-image: radial-gradient(circle at 20% 20%, #F16029 0, transparent 42%), radial-gradient(circle at 80% 80%, #19499B 0, transparent 40%);"></div>
                <div class="relative">
                    <a href="{{ route('home') }}" class="inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-white rounded-lg">
                        <x-brand-logo size="lg" variant="dark" />
                    </a>
                </div>
                <div class="relative max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-accent">Digital Jobs for Youth in Health</p>
                    <h1 class="mt-4 font-display font-bold text-3xl xl:text-4xl tracking-tight leading-tight">
                        Learn practical skills as a Digital Community Champion
                    </h1>
                    <p class="mt-4 text-slate-300 leading-relaxed">
                        Access courses, track your progress, and grow your impact through the KoraLink Agents Academy.
                    </p>
                </div>
                <p class="relative text-sm text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </p>
            </aside>

            <div class="flex flex-col justify-center px-4 py-10 sm:px-8 lg:px-12 xl:px-16">
                <div class="lg:hidden mb-8 flex justify-center">
                    <a href="{{ route('home') }}" class="inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded-lg">
                        <x-brand-logo size="lg" />
                    </a>
                </div>

                <div class="w-full max-w-md mx-auto">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-sm text-slate-500">
                        <a href="{{ route('home') }}" class="font-medium text-primary hover:text-accent-dark transition">← Back to home</a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
