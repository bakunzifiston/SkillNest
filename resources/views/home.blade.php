@extends('layouts.site')

@section('title', 'Home')

@section('content')
    {{-- Hero Banner --}}
    <section class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white overflow-hidden">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.03\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-80"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative">
            <div class="max-w-2xl">
                <h1 class="font-display font-bold text-4xl sm:text-5xl lg:text-6xl tracking-tight text-white">Learn skills that matter. <span class="text-amber-400">Build your future.</span></h1>
                <p class="mt-6 text-lg text-slate-300">Join thousands of learners on SkillNest. Expert-led courses in development, design, business, and more. Start free today.</p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition shadow-lg shadow-amber-500/25">Explore Courses</a>
                    <a href="{{ route('about') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-slate-500 text-slate-200 hover:bg-white/5 font-medium transition">About Us</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Course Categories --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <h2 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">Browse by category</h2>
                <p class="mt-3 text-slate-600">Find the path that fits your goals</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                @foreach($categories as $category)
                <a href="{{ route('courses.index', ['category' => $category->slug]) }}" class="group block p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-amber-200 hover:bg-amber-50/50 transition">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl group-hover:bg-amber-200 transition">{{ $category->icon ?? '📚' }}</div>
                    <h3 class="mt-4 font-display font-semibold text-slate-900 group-hover:text-amber-700">{{ $category->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $category->courses_count }} courses</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Latest Courses --}}
    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">Latest courses</h2>
                    <p class="mt-2 text-slate-600">New content added regularly</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold inline-flex items-center gap-1">View all courses <span aria-hidden="true">→</span></a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($latestCourses as $course)
                <a href="{{ route('courses.show', $course) }}" class="group block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-amber-100 transition">
                    <div class="aspect-video bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-4xl text-slate-400">
                        @if($course->banner_url)
                            <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover">
                        @else
                            📖
                        @endif
                    </div>
                    <div class="p-5">
                        <span class="text-xs font-medium text-amber-600 uppercase tracking-wide">{{ $course->category->name ?? 'Course' }}</span>
                        <h3 class="mt-2 font-display font-semibold text-slate-900 group-hover:text-amber-700 line-clamp-2">{{ $course->title }}</h3>
                        <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit($course->description, 80) }}</p>
                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $course->duration ?? 'Self-paced' }}</span>
                            <span class="font-semibold text-amber-600">@if($course->price > 0) ${{ number_format($course->price, 0) }} @else Free @endif</span>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Partner Logos --}}
    <section class="py-16 lg:py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-slate-900">Trusted by teams everywhere</h2>
                <p class="mt-2 text-slate-500">Companies and organizations that use SkillNest for learning</p>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-12 lg:gap-16 opacity-70 grayscale hover:grayscale-0 hover:opacity-100 transition">
                @foreach($partners as $partner)
                <div class="flex items-center justify-center" style="height: 56px;">
                    @if($partner->logo_url ?? null)
                        <img src="{{ $partner->logo_url }}" alt="{{ $partner->name ?? 'Partner' }}" class="h-full w-auto max-w-[260px] object-contain object-center" loading="lazy">
                    @else
                        <span class="text-lg font-display font-bold text-slate-400">{{ $partner->name ?? 'Partner' }}</span>
                    @endif
                </div>
                @endforeach
            </div>
            @if($partners->isEmpty())
                <p class="text-sm text-slate-400">Add partner logos in Admin → Partners.</p>
            @endif
        </div>
    </section>
@endsection
