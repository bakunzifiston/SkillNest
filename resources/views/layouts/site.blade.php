<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Learn Online') — {{ config('app.name') }}</title>
    <x-brand-meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Header menu: keep every link on one line (Kinyarwanda words are longer), and make room on laptop widths. */
        /* ---- Shared page banner + floating menu. A page opts in by giving the body the class "has-banner" (through the body_class section). ---- */
        body.has-banner .site-header { position: fixed; top: 0; left: 0; right: 0; background: transparent; border: 0; backdrop-filter: none; z-index: 60; pointer-events: none; }
        body.has-banner .site-header-bar { pointer-events: auto; width: calc(100% - 2rem); max-width: 72rem; margin: .9rem auto 0; padding-left: 1.5rem; padding-right: 1.5rem; background: #fff; border-radius: 999px; box-shadow: 0 14px 34px -14px rgba(11, 27, 51, .55); }
        body.has-banner .site-header-bar > .flex { height: 4.25rem; }
        @media (max-width: 1023px) {
            body.has-banner .site-header-bar { width: auto; margin: .6rem .75rem 0; padding-left: 1rem; padding-right: 1rem; border-radius: 1.75rem; }
        }
        .page-banner { position: relative; overflow: hidden; color: #fff; background: linear-gradient(100deg, #102C5E 0%, #153F86 46%, #19499B 100%); }
        .page-banner::after { content: ''; position: absolute; left: 0; right: 0; bottom: -1px; height: clamp(2.25rem, 6vw, 4.5rem); background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' preserveAspectRatio='none'%3E%3Cpath d='M0,0 C480,100 960,100 1440,0 L1440,100 L0,100 Z' fill='%23F8FAFC'/%3E%3C/svg%3E") center / 100% 100% no-repeat; pointer-events: none; }
        .page-banner--white::after { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 100' preserveAspectRatio='none'%3E%3Cpath d='M0,0 C480,100 960,100 1440,0 L1440,100 L0,100 Z' fill='%23FFFFFF'/%3E%3C/svg%3E"); }
        .page-banner > .relative { padding-bottom: 4.5rem; }
        body.has-banner .page-banner > .relative { padding-top: 7.25rem; }
        .page-banner-photo-overlay { background: linear-gradient(100deg, rgba(16, 44, 94, .94) 0%, rgba(21, 63, 134, .82) 55%, rgba(25, 73, 155, .62) 100%); }
        @media (max-width: 767px) { body.has-banner .page-banner > .relative { padding-top: 6.5rem; } }

        .site-nav a { white-space: nowrap; }
        @media (min-width: 1024px) and (max-width: 1279px) {
            .site-nav { gap: 1rem !important; }
            .site-partner-note { display: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col @yield('body_class')" x-data="{ mobileOpen: false }">
    <header class="site-header bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-50">
        <div class="site-header-bar max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-[4.25rem] lg:h-20 gap-4">
                <a href="{{ route('home') }}" class="flex items-center min-w-0 shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-lg">
                    <x-brand-logo size="md" />
                </a>

                <div class="hidden md:flex items-center gap-2 shrink-0" style="border-left: 1px solid #e2e8f0; padding-left: 1rem;">
                    <span class="site-partner-note text-xs text-slate-500 leading-tight" style="max-width: 5.5rem;">{{ __('In partnership with') }}</span>
                    <img src="{{ asset('images/partners/mastercard.webp') }}" alt="Mastercard" style="height: 34px; width: auto;" loading="eager">
                </div>

                <nav class="site-nav hidden lg:flex items-center gap-7" aria-label="{{ __('Primary') }}">
                    <a href="{{ route('home') }}" class="text-sm font-medium transition {{ request()->routeIs('home') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">{{ __('Home') }}</a>
                    <a href="{{ route('courses.index') }}" class="text-sm font-medium transition {{ request()->routeIs('courses.*') && ! request()->routeIs('courses.my-courses') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">{{ __('Courses') }}</a>
                    <a href="{{ route('bundles.index') }}" class="text-sm font-medium transition {{ request()->routeIs('bundles.*') && ! request()->routeIs('bundles.my-bundles') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">{{ __('Bundles') }}</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium transition {{ request()->routeIs('about') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">{{ __('About') }}</a>
                    <a href="{{ route('contact') }}" class="text-sm font-medium transition {{ request()->routeIs('contact') ? 'text-primary' : 'text-slate-600 hover:text-primary' }}">{{ __('Contact') }}</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <x-language-switcher class="hidden lg:inline-flex" />
                    @auth
                        <div class="hidden md:flex items-center gap-4 mr-1">
                            @if(auth()->user()->canAccessAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('admin.*') ? 'text-primary' : '' }}">{{ __('Admin') }}</a>
                            @else
                                <a href="{{ route('courses.my-courses') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('courses.my-courses') ? 'text-primary' : '' }}">{{ __('My Learning') }}</a>
                                <a href="{{ route('profile.edit') }}" class="text-sm font-medium text-slate-600 hover:text-primary {{ request()->routeIs('profile.*') ? 'text-primary' : '' }}">{{ __('Profile') }}</a>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">{{ __('Log out') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-primary-light font-medium text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">{{ __('Log in') }}</a>
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-accent text-white hover:bg-accent-dark font-medium text-sm shadow-brand-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">{{ __('Explore Courses') }}</a>
                    @endauth

                    <button
                        type="button"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-controls="mobile-nav"
                        aria-label="{{ __('Toggle menu') }}"
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
                <nav class="flex flex-col gap-1" aria-label="{{ __('Mobile') }}">
                    <a href="{{ route('home') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium {{ request()->routeIs('home') ? 'bg-primary-light text-primary' : 'text-slate-700 hover:bg-slate-50' }}" @click="mobileOpen = false">{{ __('Home') }}</a>
                    <a href="{{ route('courses.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Courses') }}</a>
                    <a href="{{ route('bundles.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Bundles') }}</a>
                    <a href="{{ route('about') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('About') }}</a>
                    <a href="{{ route('contact') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Contact') }}</a>
                    @auth
                        @if(auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Admin') }}</a>
                        @else
                            <a href="{{ route('courses.my-courses') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('My Learning') }}</a>
                            <a href="{{ route('profile.edit') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Profile') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="px-3 pt-2">
                            @csrf
                            <button type="submit" class="w-full text-left px-0 py-2.5 text-sm font-medium text-slate-700">{{ __('Log out') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium text-slate-700 hover:bg-slate-50" @click="mobileOpen = false">{{ __('Register') }}</a>
                        <a href="{{ route('courses.index') }}" class="mt-2 mx-3 inline-flex justify-center items-center px-4 py-2.5 rounded-xl bg-accent text-white font-medium text-sm" @click="mobileOpen = false">{{ __('Explore Courses') }}</a>
                    @endauth
                </nav>
                <div class="mt-3 mx-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-sm text-slate-500">{{ __('Language') }}</span>
                    <x-language-switcher />
                </div>
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
                        {{ __('The official learning platform for KoraLink Agents — building digital skills for community impact under Digital Jobs for Youth in Health.') }}
                    </p>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">{{ __('Explore') }}</h2>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('home') }}" class="text-sm hover:text-accent transition">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('courses.index') }}" class="text-sm hover:text-accent transition">{{ __('Courses') }}</a></li>
                        <li><a href="{{ route('bundles.index') }}" class="text-sm hover:text-accent transition">{{ __('Bundles') }}</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm hover:text-accent transition">{{ __('About') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm hover:text-accent transition">{{ __('Contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">{{ __('Learning') }}</h2>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('courses.index') }}" class="text-sm hover:text-accent transition">{{ __('All Courses') }}</a></li>
                        <li><a href="{{ route('home') }}#categories" class="text-sm hover:text-accent transition">{{ __('Categories') }}</a></li>
                        <li><a href="{{ route('bundles.index') }}" class="text-sm hover:text-accent transition">{{ __('Bundles') }}</a></li>
                        @auth
                            @unless(auth()->user()->canAccessAdmin())
                                <li><a href="{{ route('courses.my-courses') }}" class="text-sm hover:text-accent transition">{{ __('My Learning') }}</a></li>
                            @endunless
                        @endauth
                    </ul>
                </div>

                <div>
                    <h2 class="font-display font-semibold text-white text-sm tracking-wide mb-4">{{ __('Account') }}</h2>
                    <ul class="space-y-2.5">
                        @guest
                            <li><a href="{{ route('login') }}" class="text-sm hover:text-accent transition">{{ __('Log in') }}</a></li>
                            <li><a href="{{ route('register') }}" class="text-sm hover:text-accent transition">{{ __('Register') }}</a></li>
                        @else
                            @if(auth()->user()->canAccessAdmin())
                                <li><a href="{{ route('admin.dashboard') }}" class="text-sm hover:text-accent transition">{{ __('Admin') }}</a></li>
                            @else
                                <li><a href="{{ route('courses.my-courses') }}" class="text-sm hover:text-accent transition">{{ __('My Learning') }}</a></li>
                                <li><a href="{{ route('profile.edit') }}" class="text-sm hover:text-accent transition">{{ __('Profile') }}</a></li>
                            @endif
                        @endguest
                        <li><a href="{{ route('contact') }}" class="text-sm hover:text-accent transition">{{ __('Contact us') }}</a></li>
                    </ul>
                    @if(config('mail.from.address'))
                        <p class="mt-5 text-sm text-slate-400 break-all">{{ config('mail.from.address') }}</p>
                    @endif
                </div>
            </div>

            <div class="mt-12 pt-8 border-t border-white/10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm text-slate-500">
                <p>&copy; {{ date('Y') }} {{ __('KoraLink Agents Academy. All rights reserved.') }}</p>
                <div class="flex flex-wrap items-center gap-4">
                    <p>Digital Jobs for Youth in Health</p>
                    <x-language-switcher variant="dark" />
                </div>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
