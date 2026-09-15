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
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="{{ $navLink(request()->routeIs('admin.dashboard')) }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'dashboard', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="space-y-0.5">
                    <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">Catalog</p>
                    <a href="{{ route('admin.categories.index') }}" class="{{ $navLink(request()->routeIs('admin.categories.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'folder', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('admin.instructors.index') }}" class="{{ $navLink(request()->routeIs('admin.instructors.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'instructor', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Instructors</span>
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="{{ $navLink(request()->routeIs('admin.courses.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'book', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Courses</span>
                    </a>
                    <a href="{{ route('admin.bundles.index') }}" class="{{ $navLink(request()->routeIs('admin.bundles.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'bundle', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Bundles</span>
                    </a>
                    <a href="{{ route('admin.live-sessions.index') }}" class="{{ $navLink(request()->routeIs('admin.live-sessions.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'live', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Live sessions</span>
                    </a>
                </div>
                <div class="space-y-0.5">
                    <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">Learners</p>
                    <a href="{{ route('admin.users.index') }}" class="{{ $navLink(request()->routeIs('admin.users.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'users', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.imports.students.create') }}" class="{{ $navLink(request()->routeIs('admin.imports.students.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'import', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Import students</span>
                    </a>
                    <a href="{{ route('admin.course-progress.index') }}" class="{{ $navLink(request()->routeIs('admin.course-progress.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'pulse', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Student progress</span>
                    </a>
                </div>
                <div class="space-y-0.5">
                    <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">Assessments</p>
                    <a href="{{ route('admin.quizzes.index') }}" class="{{ $navLink(request()->routeIs('admin.quizzes.*') || request()->routeIs('admin.questions.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'quiz', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Quizzes</span>
                    </a>
                    <a href="{{ route('admin.quiz-results.index') }}" class="{{ $navLink(request()->routeIs('admin.quiz-results.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'results', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Quiz results</span>
                    </a>
                </div>
                <div class="space-y-0.5">
                    <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">Analytics</p>
                    <a href="{{ route('admin.reports.index') }}" class="{{ $navLink(request()->routeIs('admin.reports.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'chart', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Reports & Analytics</span>
                    </a>
                </div>
                <div class="space-y-0.5">
                    <p class="px-3 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">Site</p>
                    <a href="{{ route('admin.settings.edit') }}" class="{{ $navLink(request()->routeIs('admin.settings.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'settings', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Settings</span>
                    </a>
                    <a href="{{ route('admin.partners.index') }}" class="{{ $navLink(request()->routeIs('admin.partners.*')) }}" @click="sidebarOpen = false">
                        @include('admin.partials.dashboard-icon', ['icon' => 'image', 'class' => 'h-4 w-4 shrink-0 opacity-90'])
                        <span>Partner logos</span>
                    </a>
                </div>
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
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
