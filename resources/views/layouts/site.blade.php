<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Learn Online') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|dm-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'DM Sans', sans-serif; } .font-display { font-family: 'Outfit', sans-serif; }</style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    @if(!empty($siteLogoUrl))
                        <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name') }}" class="h-10 w-auto object-contain" style="max-width: 300px;">
                    @else
                        <span class="font-display font-bold text-xl text-slate-900">{{ config('app.name') }}</span>
                    @endif
                </a>
                <nav class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('home') ? 'text-amber-600' : '' }}">Home</a>
                    <a href="{{ route('courses.index') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('courses.*') ? 'text-amber-600' : '' }}">Courses</a>
                    <a href="{{ route('bundles.index') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('bundles.*') ? 'text-amber-600' : '' }}">Bundles</a>
                    <a href="{{ route('about') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('about') ? 'text-amber-600' : '' }}">About</a>
                    <a href="{{ route('contact') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('contact') ? 'text-amber-600' : '' }}">Contact</a>
                    @auth
                        @if(!(auth()->user()->is_admin ?? false))
                            <a href="{{ route('courses.my-courses') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('courses.my-courses') ? 'text-amber-600' : '' }}">My courses</a>
                            <a href="{{ route('bundles.my-bundles') }}" class="text-slate-600 hover:text-amber-600 font-medium {{ request()->routeIs('bundles.my-bundles') ? 'text-amber-600' : '' }}">My bundles</a>
                        @endif
                        @if(auth()->user()->is_admin ?? false)
                            <a href="{{ route('admin.dashboard') }}" class="text-amber-600 hover:text-amber-700 font-medium">Admin</a>
                        @endif
                    @endauth
                </nav>
                <div class="flex items-center gap-3">
                    @auth
@if(!(auth()->user()->is_admin ?? false))
                        <a href="{{ route('courses.my-courses') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm">My courses</a>
                        <a href="{{ route('bundles.my-bundles') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm">My bundles</a>
                        @endif
                        <a href="{{ auth()->user()->is_admin ? route('admin.dashboard') : route('dashboard') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-800 text-white hover:bg-slate-700 font-medium text-sm">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm">Log in</a>
                        <a href="{{ route('register') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-lg text-amber-600 hover:text-amber-700 font-semibold text-sm">Sign up</a>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-amber-500 text-white hover:bg-amber-600 font-medium text-sm">Get Started</a>
                    @endauth
                </div>
            </div>
            <div class="md:hidden border-t border-slate-100 px-4 py-3 flex flex-wrap gap-2">
                <a href="{{ route('home') }}" class="text-sm text-slate-600 hover:text-amber-600">Home</a>
                <a href="{{ route('courses.index') }}" class="text-sm text-slate-600 hover:text-amber-600">Courses</a>
                <a href="{{ route('bundles.index') }}" class="text-sm text-slate-600 hover:text-amber-600">Bundles</a>
                <a href="{{ route('about') }}" class="text-sm text-slate-600 hover:text-amber-600">About</a>
                <a href="{{ route('contact') }}" class="text-sm text-slate-600 hover:text-amber-600">Contact</a>
                @auth
                    @if(!(auth()->user()->is_admin ?? false))
                        <a href="{{ route('courses.my-courses') }}" class="text-sm text-slate-600 hover:text-amber-600">My courses</a>
                    <a href="{{ route('bundles.my-bundles') }}" class="text-sm text-slate-600 hover:text-amber-600">My bundles</a>
                    @endif
                    @if(auth()->user()->is_admin ?? false)
                        <a href="{{ route('admin.dashboard') }}" class="text-sm text-amber-600 font-medium">Admin</a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <div class="lg:col-span-1">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                    @if(!empty($siteLogoUrl))
                        <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain brightness-0 invert" style="max-width: 260px;">
                    @else
                        <span class="font-display font-bold text-xl text-white">{{ config('app.name') }}</span>
                    @endif
                </a>
                    <p class="mt-3 text-sm text-slate-400 max-w-xs">Learn in-demand skills with expert-led courses.</p>
                </div>
                <div>
                    <h4 class="font-display font-semibold text-white mb-4">Explore</h4>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-sm hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('courses.index') }}" class="text-sm hover:text-white transition">All Courses</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm hover:text-white transition">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-display font-semibold text-white mb-4">Categories</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('courses.index') }}?category=development" class="hover:text-white transition">Development</a></li>
                        <li><a href="{{ route('courses.index') }}?category=design" class="hover:text-white transition">Design</a></li>
                        <li><a href="{{ route('courses.index') }}?category=business" class="hover:text-white transition">Business</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-display font-semibold text-white mb-4">Contact</h4>
                    <p class="text-sm text-slate-400">bakunzifiston@gmail.com</p>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-slate-800 text-center text-sm text-slate-500">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
