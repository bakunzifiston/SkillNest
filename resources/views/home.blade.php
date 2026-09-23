@extends('layouts.site')

@section('title', 'Home')

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 opacity-[0.07]" aria-hidden="true" style="background-image: radial-gradient(circle at 20% 20%, #F16029 0, transparent 40%), radial-gradient(circle at 80% 0%, #19499B 0, transparent 35%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 lg:py-20">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                <div class="lg:col-span-6">
                    <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.18em] text-accent">KoraLink Agents Academy</p>
                    <h1 class="mt-4 font-display font-bold text-4xl sm:text-5xl lg:text-[3.25rem] leading-[1.08] tracking-tight text-white">
                        Build Digital Skills.<br class="hidden sm:block">
                        <span class="text-accent">Grow Your Impact.</span>
                    </h1>
                    <p class="mt-6 text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl">
                        The official learning platform for KoraLink Agents under the Digital Jobs for Youth in Health program. Develop practical digital, marketplace, and professional skills to serve your community with confidence.
                    </p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition shadow-brand-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-navy">
                            Explore Courses
                        </a>
                        <a href="{{ route('about') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-navy">
                            Learn About KoraLink
                        </a>
                    </div>
                    <p class="mt-8 text-sm text-slate-400">
                        Practical training for Digital Community Champions
                    </p>
                </div>

                <div class="lg:col-span-6">
                    <div class="relative max-w-lg mx-auto lg:max-w-none lg:ml-auto">
                        <div class="relative aspect-[4/3] rounded-2xl overflow-hidden shadow-brand ring-1 ring-white/10">
                            <img
                                src="{{ asset('images/banner/agents-workshop.jpg') }}"
                                alt="KoraLink agents learning together in a training workshop"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                fetchpriority="high"
                            >
                        </div>
                        <div class="absolute -bottom-6 -left-2 sm:left-4 w-[48%] sm:w-[44%] aspect-[4/3] rounded-xl overflow-hidden shadow-xl ring-2 ring-navy">
                            <img
                                src="{{ asset('images/banner/agents-training.jpg') }}"
                                alt="KoraLink agents during a hands-on training session"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="lazy"
                            >
                        </div>
                        <div class="absolute -top-3 right-2 sm:right-6 rounded-xl bg-white text-navy px-3.5 py-2.5 shadow-lg max-w-[11rem]">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary">Youth in Health</p>
                            <p class="mt-0.5 text-xs text-slate-600 leading-snug">Digital skills for community impact</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="h-8 sm:h-12 bg-slate-50" aria-hidden="true"></div>
    </section>

    {{-- Categories --}}
    <section id="categories" class="py-14 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Browse by category</h2>
                <p class="mt-2 text-slate-600">Find learning paths that match the skills you need to grow.</p>
            </div>

            @if($categories->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($categories as $category)
                        <a
                            href="{{ route('courses.index', ['category' => $category->slug]) }}"
                            class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                        >
                            <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-light text-primary group-hover:bg-primary group-hover:text-white transition">
                                @include('partials.category-icon', ['slug' => $category->slug, 'name' => $category->name, 'class' => 'h-5 w-5'])
                            </span>
                            <span class="mt-4 font-display font-semibold text-navy group-hover:text-primary transition">{{ $category->name }}</span>
                            <span class="mt-1 text-sm text-slate-500">
                                {{ $category->courses_count }} {{ \Illuminate\Support\Str::plural('course', $category->courses_count) }}
                            </span>
                            <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium text-primary opacity-80 group-hover:opacity-100">
                                Browse
                                <svg class="h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Categories will appear here once they are added.</p>
            @endif
        </div>
    </section>

    {{-- Featured courses --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div class="max-w-2xl">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Start Learning Today</h2>
                    <p class="mt-2 text-slate-600">Explore practical courses designed to help you build skills and grow your impact.</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-primary hover:text-accent-dark font-semibold inline-flex items-center gap-1 shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                    View All Courses
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            @if($latestCourses->isNotEmpty())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    @foreach($latestCourses as $course)
                        @php
                            $lessonCount = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
                        @endphp
                        <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary-muted hover:shadow-brand transition">
                            <a href="{{ route('courses.show', $course) }}" class="block aspect-[16/10] bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary">
                                @if($course->banner_url)
                                    <img
                                        src="{{ $course->banner_url }}"
                                        alt=""
                                        class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-light to-slate-100 text-primary">
                                        @include('partials.category-icon', ['slug' => $course->category->slug ?? '', 'name' => $course->category->name ?? '', 'class' => 'h-10 w-10'])
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($course->category)
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-[11px] font-semibold uppercase tracking-wide">{{ $course->category->name }}</span>
                                    @endif
                                    @if(($course->price ?? 0) > 0)
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-[11px] font-semibold">${{ number_format($course->price, 0) }}</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-[11px] font-semibold">Free</span>
                                    @endif
                                </div>
                                <h3 class="mt-3 font-display font-semibold text-lg text-navy leading-snug">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        {{ $course->title }}
                                    </a>
                                </h3>
                                @if(filled($course->description))
                                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit(trim(preg_replace('/\s+/', ' ', $course->description)), 110) }}</p>
                                @endif
                                <div class="mt-auto pt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                    @if($course->instructor)
                                        <span>{{ $course->instructor->name }}</span>
                                    @endif
                                    @if(filled($course->duration))
                                        <span>{{ $course->duration }}</span>
                                    @endif
                                    @if($lessonCount > 0)
                                        <span>{{ $lessonCount }} {{ \Illuminate\Support\Str::plural('lesson', $lessonCount) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('courses.show', $course) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
                                    View course
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">Courses will appear here once they are published.</p>
            @endif
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-16 lg:py-20 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">How KoraLink Academy Works</h2>
                <p class="mt-2 text-slate-600">A clear path from registration to completed learning.</p>
            </div>
            <ol class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach([
                    ['01', 'Create Your Account', 'Register and access your learner profile.'],
                    ['02', 'Explore Courses', 'Browse by category and find the skills you need.'],
                    ['03', 'Learn at Your Pace', 'Access lessons and track your progress as you go.'],
                    ['04', 'Complete & Grow', 'Finish your journey and keep building new skills.'],
                ] as [$step, $title, $copy])
                    <li class="rounded-2xl bg-white border border-slate-200 p-5">
                        <span class="font-display font-bold text-2xl text-accent tabular-nums">{{ $step }}</span>
                        <h3 class="mt-3 font-display font-semibold text-navy">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $copy }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Academy / Program --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-5 order-2 lg:order-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent">Program</p>
                    <h2 class="mt-3 font-display font-bold text-2xl lg:text-3xl text-navy">KoraLink Agents Academy</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        The official learning platform supporting Digital Jobs for Youth in Health and the development of Digital Community Champions.
                    </p>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        The Academy provides practical learning resources that help agents strengthen digital skills, use digital marketplaces effectively, deliver better services, and contribute to stronger communities.
                    </p>
                    <a href="{{ route('about') }}" class="mt-8 inline-flex items-center px-6 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Learn More About KoraLink
                    </a>
                </div>
                <div class="lg:col-span-7 order-1 lg:order-2">
                    <div class="grid grid-cols-12 gap-3 sm:gap-4">
                        <div class="col-span-7 relative aspect-[4/5] rounded-2xl overflow-hidden">
                            <img src="{{ asset('images/banner/agents-field.jpg') }}" alt="KoraLink agents working in the community" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        </div>
                        <div class="col-span-5 flex flex-col gap-3 sm:gap-4">
                            <div class="relative flex-1 min-h-[7rem] rounded-2xl overflow-hidden">
                                <img src="{{ asset('images/banner/agents-workshop.jpg') }}" alt="Agents collaborating in a workshop" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            </div>
                            <div class="relative flex-1 min-h-[7rem] rounded-2xl overflow-hidden">
                                <img src="{{ asset('images/banner/agents-training.jpg') }}" alt="Agents in a training session" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust / impact --}}
    <section class="py-14 lg:py-16 bg-primary-light">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $hasStats = ($stats['learners'] ?? 0) > 0 || ($stats['courses'] ?? 0) > 0 || ($stats['categories'] ?? 0) > 0;
            @endphp
            @if($hasStats)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    @if(($stats['learners'] ?? 0) > 0)
                        <div class="rounded-2xl bg-white/70 border border-primary-muted/40 px-5 py-6 text-center">
                            <p class="font-display font-bold text-3xl text-navy tabular-nums">{{ number_format($stats['learners']) }}</p>
                            <p class="mt-1 text-sm text-slate-600">Learners</p>
                        </div>
                    @endif
                    @if(($stats['courses'] ?? 0) > 0)
                        <div class="rounded-2xl bg-white/70 border border-primary-muted/40 px-5 py-6 text-center">
                            <p class="font-display font-bold text-3xl text-navy tabular-nums">{{ number_format($stats['courses']) }}</p>
                            <p class="mt-1 text-sm text-slate-600">Courses</p>
                        </div>
                    @endif
                    @if(($stats['categories'] ?? 0) > 0)
                        <div class="rounded-2xl bg-white/70 border border-primary-muted/40 px-5 py-6 text-center">
                            <p class="font-display font-bold text-3xl text-navy tabular-nums">{{ number_format($stats['categories']) }}</p>
                            <p class="mt-1 text-sm text-slate-600">Categories</p>
                        </div>
                    @endif
                    @if(($stats['enrollments'] ?? 0) > 0)
                        <div class="rounded-2xl bg-white/70 border border-primary-muted/40 px-5 py-6 text-center">
                            <p class="font-display font-bold text-3xl text-navy tabular-nums">{{ number_format($stats['enrollments']) }}</p>
                            <p class="mt-1 text-sm text-slate-600">Enrollments</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach([
                        'Practical digital learning',
                        'Community-focused training',
                        'Skills for the digital economy',
                        'Supported by program partners',
                    ] as $statement)
                        <div class="rounded-2xl bg-white/70 border border-primary-muted/40 px-5 py-5">
                            <p class="font-display font-semibold text-navy text-sm leading-snug">{{ $statement }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Partners --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Our Partners</h2>
                <p class="mt-2 text-slate-600">Working together to strengthen digital skills, innovation, and community impact.</p>
            </div>

            @if($partners->isNotEmpty())
                <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($partners as $partner)
                        <li class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-6 min-h-[7.5rem]">
                            @if($partner->logo_url)
                                <img
                                    src="{{ $partner->logo_url }}"
                                    alt="{{ $partner->name ?: 'Partner organization' }}"
                                    class="h-12 w-auto max-w-full object-contain"
                                    loading="lazy"
                                >
                            @endif
                            @if(filled($partner->name))
                                <span class="text-xs sm:text-sm font-medium text-slate-600 text-center">{{ $partner->name }}</span>
                            @elseif(! $partner->logo_url)
                                <span class="text-sm text-slate-400">Partner</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-center text-sm text-slate-500">Partner logos will appear here once they are added.</p>
            @endif
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-16 lg:py-20 bg-navy text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display font-bold text-2xl sm:text-3xl lg:text-4xl tracking-tight">Ready to grow your digital skills?</h2>
            <p class="mt-4 text-slate-300 leading-relaxed">
                Explore practical courses designed to help you learn, improve your work, and create greater impact in your community.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    Explore Courses
                </a>
                <a href="{{ route('about') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    About KoraLink
                </a>
            </div>
        </div>
    </section>
@endsection
