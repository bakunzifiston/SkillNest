@extends('layouts.site')

@section('title', 'Home')

@section('content')
    {{-- Hero Banner --}}
    @php
        $bannerSlides = [
            ['src' => asset('images/banner/agents-field.jpg'), 'alt' => 'KoraLink agents collaborating in the field'],
            ['src' => asset('images/banner/agents-workshop.jpg'), 'alt' => 'KoraLink agents in a training workshop'],
            ['src' => asset('images/banner/agents-training.jpg'), 'alt' => 'KoraLink agents at a training session'],
        ];
    @endphp
    <section
        class="relative text-white overflow-hidden min-h-[28rem] sm:min-h-[32rem] lg:min-h-[36rem]"
        x-data="{
            current: 0,
            total: {{ count($bannerSlides) }},
            timer: null,
            next() { this.current = (this.current + 1) % this.total },
            prev() { this.current = (this.current - 1 + this.total) % this.total },
            go(i) { this.current = i },
            start() { this.stop(); this.timer = setInterval(() => this.next(), 5500) },
            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null } },
        }"
        x-init="start()"
        @mouseenter="stop()"
        @mouseleave="start()"
        @focusin="stop()"
        @focusout="start()"
        role="region"
        aria-roledescription="carousel"
        aria-label="KoraLink Agents banner"
    >
        <div class="absolute inset-0">
            @foreach($bannerSlides as $index => $slide)
                <img
                    src="{{ $slide['src'] }}"
                    alt="{{ $slide['alt'] }}"
                    class="absolute inset-0 w-full h-full object-cover object-center transition-opacity duration-700 ease-out"
                    :class="current === {{ $index }} ? 'opacity-100' : 'opacity-0'"
                    @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                >
            @endforeach
            <div class="absolute inset-0 bg-gradient-to-r from-navy/85 via-navy/60 to-navy/35"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 relative flex items-center min-h-[28rem] sm:min-h-[32rem] lg:min-h-[36rem]">
            <div class="max-w-2xl">
                <h1 class="font-display font-bold text-4xl sm:text-5xl lg:text-6xl tracking-tight text-white">KoraLink <span class="text-accent">Agents</span></h1>
                <p class="mt-6 text-lg text-slate-200">On this platform, you will find courses that help you learn how to use the Marketplace effectively, provide excellent service to your customers, and grow your business.</p>
                <div class="mt-10 flex flex-wrap gap-4">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition shadow-brand-accent">Explore Courses</a>
                    <a href="{{ route('about') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-white/30 text-white hover:bg-white/10 font-medium transition">About Us</a>
                </div>
            </div>
        </div>

        <button type="button" class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/15 hover:bg-white/30 border border-white/30 text-white flex items-center justify-center backdrop-blur-sm" @click="prev(); start()" aria-label="Previous slide">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/15 hover:bg-white/30 border border-white/30 text-white flex items-center justify-center backdrop-blur-sm" @click="next(); start()" aria-label="Next slide">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>

        <div class="absolute bottom-5 left-0 right-0 z-10 flex justify-center gap-2" role="tablist" aria-label="Banner slides">
            @foreach($bannerSlides as $index => $slide)
                <button
                    type="button"
                    class="w-2.5 h-2.5 rounded-full transition"
                    :class="current === {{ $index }} ? 'bg-accent scale-110' : 'bg-white/50 hover:bg-white/80'"
                    @click="go({{ $index }}); start()"
                    :aria-selected="current === {{ $index }}"
                    aria-label="Show slide {{ $index + 1 }}"
                ></button>
            @endforeach
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
                <a href="{{ route('courses.index', ['category' => $category->slug]) }}" class="group block p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-accent-muted hover:bg-accent-light/50 transition">
                    <div class="w-12 h-12 rounded-xl bg-accent-muted text-primary flex items-center justify-center text-2xl group-hover:bg-accent-muted transition">{{ $category->icon ?? '📚' }}</div>
                    <h3 class="mt-4 font-display font-semibold text-slate-900 group-hover:text-accent-dark">{{ $category->name }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $category->courses_count }} courses</p>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Agents Academy --}}
    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="relative overflow-hidden rounded-2xl shadow-brand aspect-[4/3] lg:aspect-auto lg:min-h-[22rem]">
                    <img
                        src="{{ asset('images/banner/agents-workshop.jpg') }}"
                        alt="KoraLink Agents Academy training session"
                        class="absolute inset-0 w-full h-full object-cover object-center"
                        loading="lazy"
                    >
                </div>
                <div>
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy">KoraLink <span class="text-accent">Agents Academy</span></h2>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">The KoraLink Agents e-learning platform is your gateway to becoming a true Digital Community Champion under the Digital Jobs for Youth in Health program. Here, you’ll gain practical digital skills, connect with other changemakers, and take the next step toward improving community health through innovation and technology.</p>
                    <a href="{{ route('courses.index') }}" class="mt-8 inline-flex items-center px-6 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">Explore Courses</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Latest Courses --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div>
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">Latest courses</h2>
                    <p class="mt-2 text-slate-600">New content added regularly</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-primary hover:text-accent-dark font-semibold inline-flex items-center gap-1">View all courses <span aria-hidden="true">→</span></a>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                @foreach($latestCourses as $course)
                    @php $isRealCourse = $course instanceof \App\Models\Course; @endphp
                    @if($isRealCourse)
                        <a href="{{ route('courses.show', $course) }}" class="group block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-accent-muted transition">
                    @else
                        <div class="group block bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    @endif
                            <div class="aspect-video bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-4xl text-slate-400">
                                @if(!empty($course->banner_url))
                                    <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    📖
                                @endif
                            </div>
                            <div class="p-5">
                                <span class="text-xs font-medium text-primary uppercase tracking-wide">{{ $course->category->name ?? 'Course' }}</span>
                                <h3 class="mt-2 font-display font-semibold text-slate-900 group-hover:text-accent-dark line-clamp-2">{{ $course->title }}</h3>
                                <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit($course->description, 80) }}</p>
                                <div class="mt-4 flex items-center justify-between text-sm">
                                    <span class="text-slate-500">{{ $course->duration ?? 'Self-paced' }}</span>
                                    <span class="font-semibold text-primary">@if(($course->price ?? 0) > 0) ${{ number_format($course->price, 0) }} @else Free @endif</span>
                                </div>
                            </div>
                    @if($isRealCourse)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

    {{-- Agents Academy — program --}}
    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy">KoraLink <span class="text-accent">Agents Academy</span></h2>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">The official e-learning platform of the Digital Jobs for Youth in Health (KoraLink Agents) program, dedicated to the success of our Digital Community Champions.</p>
                </div>
                <div class="relative overflow-hidden rounded-2xl shadow-brand aspect-[4/3] lg:aspect-auto lg:min-h-[22rem] order-1 lg:order-2">
                    <img
                        src="{{ asset('images/banner/agents-field.jpg') }}"
                        alt="KoraLink agents working together in the community"
                        class="absolute inset-0 w-full h-full object-cover object-center"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </section>

    {{-- Partner Logos --}}
    <section class="py-16 lg:py-20 bg-white border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-slate-900">Trusted by teams everywhere</h2>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-12 lg:gap-16">
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
