<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <x-brand-meta />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-screen">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-navy/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="admin-sidebar fixed inset-y-0 left-0 z-50 w-64 bg-navy text-white shrink-0 flex flex-col transition-transform duration-200 ease-out lg:static lg:z-auto lg:translate-x-0"
            :class="sidebarOpen ? 'admin-sidebar-open' : ''"
        >
            <div class="p-5 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2" @click="sidebarOpen = false">
                    <x-brand-logo variant="dark" size="sm" />
                    <span class="text-[10px] font-semibold uppercase tracking-wider bg-accent text-white px-2 py-0.5 rounded-full">Admin</span>
                </a>
            </div>
            <nav class="p-3 flex-1 overflow-y-auto space-y-5 text-sm">
                @php
                    $navLink = function (bool $active): string {
                        return 'flex items-center gap-2.5 px-3 py-2 rounded-xl transition '.($active
                            ? 'bg-primary text-white font-medium'
                            : 'text-white/80 hover:bg-white/10 hover:text-white');
                    };
                @endphp
                @foreach(($adminNavGroups ?? []) as $group)
                    <div class="space-y-0.5">
                        @if(($group['label'] ?? '') !== '')
                            <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">{{ $group['label'] }}</p>
                        @endif
                        @foreach($group['items'] as $item)
                            <a href="{{ route($item['route']) }}" class="{{ $navLink(request()->routeIs(...(array) $item['active'])) }}" @if(request()->routeIs(...(array) $item['active'])) aria-current="page" @endif @click="sidebarOpen = false">
                                @include('admin.partials.dashboard-icon', ['icon' => $item['icon'], 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
            <div class="p-3 border-t border-white/10 space-y-0.5">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm text-white/70 hover:bg-white/10 hover:text-white">
                    @include('admin.partials.dashboard-icon', ['icon' => 'external', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                    <span>View site</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-2.5 text-left px-3 py-2 rounded-xl text-sm text-white/70 hover:bg-white/10 hover:text-white">
                        @include('admin.partials.dashboard-icon', ['icon' => 'logout', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="flex-1 overflow-auto min-w-0">
            <header class="bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <button type="button" class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl border border-slate-200 text-slate-600 hover:bg-primary-light hover:text-primary" @click="sidebarOpen = true" aria-label="Open menu">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-lg sm:text-xl font-display font-semibold text-navy truncate">@yield('header', 'Admin')</h1>
                            <p class="hidden sm:block text-xs text-slate-400 truncate">KoraLink Agents Academy</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                        <span class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-full bg-slate-100 text-sm text-slate-600 truncate max-w-[10rem]">{{ auth()->user()->name }}</span>
                        <a href="{{ route('home') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-medium text-slate-600 border border-slate-200 hover:bg-primary-light hover:text-primary hover:border-primary-muted">View site</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-medium text-white bg-primary hover:bg-primary-dark">Log out</button>
                        </form>
                    </div>
                </div>
            </header>
            <div class="p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-4 px-4 py-2 bg-success-light text-success-darker rounded-xl">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-4 px-4 py-2 bg-red-50 text-red-700 rounded-xl">{{ session('error') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
