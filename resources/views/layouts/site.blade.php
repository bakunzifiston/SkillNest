<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Learn Online') — {{ config('app.name') }}</title>
    <x-brand-meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="{ mobileOpen: false }">
    <header class="bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[4.25rem] lg:h-20 gap-4">
                <a href="{{ route('home') }}" class="flex items-center min-w-0 shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-lg">
                    <x-brand-logo size="md" />
                </a>

                <nav class="hidden lg:flex items-center gap-7" aria-label="Primary">
                    <a href="{{ route('home') }}" class="text-sm font-medium transition {{ request()->routeIs('home') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">Home</a>
                    <a href="{{ route('courses.index') }}" class="text-sm font-medium transition {{ request()->routeIs('courses.*') && ! request()->routeIs('courses.my-courses') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">Courses</a>
                    <a href="{{ route('bundles.index') }}" class="text-sm font-medium transition {{ request()->routeIs('bundles.*') && ! request()->routeIs('bundles.my-bundles') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">Bundles</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium transition {{ request()->routeIs('about') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">About</a>
                    <a href="{{ route('contact') }}" class="text-sm font-medium transition {{ request()->routeIs('contact') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">Contact</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    @auth
                        <div class="hidden md:flex items-center gap-4 mr-1">
                            @if(auth()->user()->canAccessAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('admin.*') ? 'text-primary' : '' }}">Admin</a>
                            @else
                                <a href="{{ route('courses.my-courses') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('courses.my-courses') ? 'text-primary' : '' }}">My Learning</a>
                                <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('profile.*') ? 'text-primary' : '' }}">Profile</a>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-primary-light font-medium text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">Log in</a>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-accent text-white hover:bg-accent-dark font-medium text-sm shadow-brand-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">Explore Courses</a>
                    @endauth

                    <button
                        type="button"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-controls="mobile-nav"
                        aria-label="Toggle menu"
                    >
                        <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <div
                id="mobile-nav"
                x-show="mobileOpen"
                x-cloak
                x-transition
                class="lg:hidden border-t border-slate-100 py-4"
            >
                <nav class="flex flex-col gap-1" aria-label="Mobile">
                    <a href="{{ route('home') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('home') ? 'bg-primary-light text-primary' : 'text-slate-700 hover:bg-slate-50' }}" @click="mobileOpen = false">Home</a>
                    <a href="{{ route('courses.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Courses</a>
                    <a href="{{ route('bundles.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Bundles</a>
                    <a href="{{ route('about') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">About</a>
                    <a href="{{ route('contact') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Contact</a>
                    @auth
                        @if(auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Admin</a>
                        @else
                            <a href="{{ route('courses.my-courses') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">My Learning</a>
                            <a href="{{ route('profile.edit') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Profile</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="px-3 pt-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-0 py-2.5 text-sm font-medium text-slate-700">Log out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Log in</a>
                        <a href="{{ route('register') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">Register</a>
                        <a href="{{ route('courses.index') }}" class="mt-2 mx-3 inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-accent text-white font-medium text-sm" @click="mobileOpen = false">Explore Courses</a>
                    @endauth
                </nav>
            </div>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-navy text-slate-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-12">
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="{{ route('home') }}" class="inline-flex focus:outline-none focus-visible:ring-2 focus-visible:ring-accent rounded-lg">
                        <x-brand-logo variant="dark" size="md" />
                    </a>
                    <p class="mt-4 text-sm text-slate-400 leading-relaxed max-w-xs">
                        The official learning platform for KoraLink Agents — building digital skills for community impact under Digital Jobs for Youth in Health.
                    </p>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">Explore</h2>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('home') }}" class="text-sm hover:text-accent transition">Home</a></li>
                        <li><a href="{{ route('courses.index') }}" class="text-sm hover:text-accent transition">Courses</a></li>
                        <li><a href="{{ route('bundles.index') }}" class="text-sm hover:text-accent transition">Bundles</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm hover:text-accent transition">About</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm hover:text-accent transition">Contact</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">Learning</h2>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('courses.index') }}" class="text-sm hover:text-accent transition">All Courses</a></li>
                        <li><a href="{{ route('home') }}#categories" class="text-sm hover:text-accent transition">Categories</a></li>
                        <li><a href="{{ route('bundles.index') }}" class="text-sm hover:text-accent transition">Bundles</a></li>
                        @auth
                            @unless(auth()->user()->canAccessAdmin())
                                <li><a href="{{ route('courses.my-courses') }}" class="text-sm hover:text-accent transition">My Learning</a></li>
                            @endunless
                        @endauth
                    </ul>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">Account</h2>
                    <ul class="space-y-2.5">
                        @guest
                            <li><a href="{{ route('login') }}" class="text-sm hover:text-accent transition">Log in</a></li>
                            <li><a href="{{ route('register') }}" class="text-sm hover:text-accent transition">Register</a></li>
                        @else
                            @if(auth()->user()->canAccessAdmin())
                                <li><a href="{{ route('admin.dashboard') }}" class="text-sm hover:text-accent transition">Admin</a></li>
                            @else
                                <li><a href="{{ route('courses.my-courses') }}" class="text-sm hover:text-accent transition">My Learning</a></li>
                                <li><a href="{{ route('profile.edit') }}" class="text-sm hover:text-accent transition">Profile</a></li>
                            @endif
                        @endguest
                        <li><a href="{{ route('contact') }}" class="text-sm hover:text-accent transition">Contact us</a></li>
                    </ul>
                    @if(config('mail.from.address'))
                        <p class="mt-5 text-sm text-slate-400 break-all">{{ config('mail.from.address') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} KoraLink Agents Academy. All rights reserved.</p>
                <p>Digital Jobs for Youth in Health</p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
