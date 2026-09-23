@extends('layouts.site')

@section('title', $bundle->title)

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
            <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('bundles.index') }}" class="hover:text-white transition">Bundles</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium truncate max-w-[16rem]">{{ $bundle->title }}</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-success-light text-success-darker border border-success-muted">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-6 p-4 rounded-xl bg-accent-light text-accent-darker border border-accent-muted">{{ session('info') }}</div>
            @endif

            <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                <div class="lg:col-span-7">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent">Course bundle</p>
                    <h1 class="mt-3 font-display font-bold text-3xl sm:text-4xl tracking-tight">{{ $bundle->title }}</h1>
                    <p class="mt-3 text-slate-300">
                        {{ $bundle->courses->count() }} {{ \Illuminate\Support\Str::plural('course', $bundle->courses->count()) }} in this learning path
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        @if($enrolled)
                            @if($bundleEnrollment)
                                <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white text-sm">
                                    <span class="font-medium">Progress</span>
                                    <span class="tabular-nums">{{ $bundleEnrollment->completed_courses }} / {{ $bundleEnrollment->total_courses }} ({{ (int) $bundleEnrollment->bundle_completion_percentage }}%)</span>
                                </div>
                                @if($bundleEnrollment->completed_at)
                                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-success text-white text-sm font-medium">Bundle completed</span>
                                @endif
                            @endif
                            <a href="{{ route('courses.my-courses') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium text-sm transition">My Learning</a>
                        @else
                            <form action="{{ route('bundles.enroll', $bundle) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                    Enroll in this bundle
                                </button>
                            </form>
                            @guest
                                <p class="text-sm text-slate-400 self-center">Sign in to enroll and unlock all courses.</p>
                            @endguest
                        @endif
                    </div>
                </div>

                @if($bundle->thumbnail_url)
                    <div class="lg:col-span-5">
                        <div class="rounded-2xl overflow-hidden ring-1 ring-white/10 shadow-brand aspect-[16/10]">
                            <img src="{{ $bundle->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-8 lg:gap-12">
                <div class="lg:col-span-5">
                    <h2 class="font-display font-bold text-xl text-navy">What you’ll get</h2>
                    <div class="mt-4 text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $bundle->description ?: 'This bundle groups related courses into one learning path.' }}</div>
                </div>

                <div class="lg:col-span-7">
                    <h2 class="font-display font-bold text-xl text-navy mb-5">Courses in this bundle</h2>
                    <ol class="space-y-3">
                        @foreach($bundle->courses as $index => $course)
                            <li class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-slate-200 hover:border-primary-muted transition">
                                <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-primary-light text-primary font-semibold flex items-center justify-center text-sm tabular-nums">{{ $index + 1 }}</span>
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('courses.show', $course) }}" class="font-medium text-navy hover:text-primary transition">{{ $course->title }}</a>
                                    @if($course->category)
                                        <p class="mt-0.5 text-sm text-slate-500">{{ $course->category->name }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('courses.show', $course) }}" class="flex-shrink-0 text-sm font-semibold text-primary hover:text-accent-dark">
                                    {{ $enrolled ? 'Open' : 'View' }}
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
@endsection
