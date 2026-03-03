<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { font-family: 'Figtree', sans-serif; }</style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="w-64 bg-slate-800 text-white shrink-0 flex flex-col">
            <div class="p-5 border-b border-slate-700">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name') }}" class="h-9 w-auto object-contain brightness-0 invert" style="max-width: 220px;">
                    <span class="font-bold text-lg">Admin</span>
                @else
                    <span class="font-bold text-lg">{{ config('app.name') }} Admin</span>
                @endif
            </a>
            </div>
            <nav class="p-3 flex-1 overflow-y-auto space-y-4">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Dashboard</a>
                </div>
                <div>
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Catalog</p>
                    <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Categories</a>
                    <a href="{{ route('admin.instructors.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.instructors.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Instructors</a>
                    <a href="{{ route('admin.courses.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.courses.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Courses</a>
                    <a href="{{ route('admin.bundles.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.bundles.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Bundles</a>
                    <a href="{{ route('admin.live-sessions.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.live-sessions.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Live sessions</a>
                </div>
                <div>
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Learners</p>
                    <a href="{{ route('admin.users.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Users</a>
                    <a href="{{ route('admin.course-progress.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.course-progress.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Student progress</a>
                </div>
                <div>
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Assessments</p>
                    <a href="{{ route('admin.quizzes.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.quizzes.*') || request()->routeIs('admin.questions.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Quizzes</a>
                    <a href="{{ route('admin.quiz-results.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.quiz-results.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Quiz results</a>
                </div>
                <div>
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Analytics</p>
                    <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.reports.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Reports & Analytics</a>
                </div>
                <div>
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">Site</p>
                    <a href="{{ route('admin.settings.edit') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Settings</a>
                    <a href="{{ route('admin.partners.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('admin.partners.*') ? 'bg-slate-700' : 'hover:bg-slate-700' }}">Partner logos</a>
                </div>
            </nav>
            <div class="p-3 border-t border-slate-700">
                <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-700 hover:text-white">View site</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-700 hover:text-white">Log out</button>
                </form>
            </div>
        </aside>
        {{-- Main --}}
        <main class="flex-1 overflow-auto">
            <header class="bg-white border-b border-gray-200 px-8 py-4">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-semibold text-gray-800">@yield('header', 'Admin')</h1>
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                </div>
            </header>
            <div class="p-8">
                @if(session('success'))
                    <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
