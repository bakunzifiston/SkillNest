@extends('layouts.site')

@push('styles')
    <script>
        /* Scroll-reveal animations: hide-until-visible only applies when JS is running. Safety: if the
           animation script below never loads, show everything after 4s so nothing stays hidden. */
        document.documentElement.classList.add('js-reveal');
        setTimeout(function () { if (!window.__revealReady) document.documentElement.classList.remove('js-reveal'); }, 4000);
    </script>
    <style>
        /* ---- Animations (home page) ---- */
        .js-reveal .reveal {
            opacity: 0; transform: translateY(22px);
            transition: opacity .65s cubic-bezier(.22, .61, .36, 1), transform .65s cubic-bezier(.22, .61, .36, 1);
            transition-delay: var(--d, 0ms);
        }
        .js-reveal .reveal.is-visible { opacity: 1; transform: none; }
        .cat-icon, .step-num { transition: transform .25s ease; }
        .cat-card:hover .cat-icon, .step-card:hover .step-num { transform: scale(1.1) rotate(-4deg); }
        @media (prefers-reduced-motion: reduce) {
            .js-reveal .reveal { opacity: 1; transform: none; transition: none; }
            .cat-card, .step-card, .stat-card, .cat-icon, .step-num, .cat-arrow { transition: none; }
            .cat-card:hover, .step-card:hover, .stat-card:hover { transform: none; }
            .cat-card:hover .cat-icon, .step-card:hover .step-num, .cat-card:hover .cat-arrow { transform: none; }
        }

        /* Category cards: coloured with the brand blue via CSS variables set inline. */
        .cat-card {
            --c: #19499B; --bg: #E8EEF7; --bd: #C9D6EE; --tx: #102C5E;
            position: relative; display: flex; flex-direction: column; overflow: hidden;
            padding: 1.25rem; border-radius: 1rem; border: 1px solid var(--bd);
            background: linear-gradient(160deg, var(--bg) 0%, #ffffff 72%);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .cat-card::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 4px; background: var(--c); }
        .cat-card:hover { transform: translateY(-3px); border-color: var(--c); box-shadow: 0 14px 28px -16px var(--c); }
        .cat-card:focus-visible { outline: 2px solid var(--c); outline-offset: 2px; }
        .cat-icon { display: inline-flex; width: 2.9rem; height: 2.9rem; align-items: center; justify-content: center; border-radius: .8rem; background: var(--c); color: #fff; box-shadow: 0 8px 16px -8px var(--c); }
        .cat-name { margin-top: 1rem; font-weight: 600; color: #0B1B33; transition: color .2s ease; }
        .cat-card:hover .cat-name { color: var(--tx); }
        .cat-count { align-self: flex-start; margin-top: .5rem; padding: .1rem .65rem; border-radius: 999px; background: var(--bg); border: 1px solid var(--bd); color: var(--tx); font-size: .75rem; font-weight: 600; }
        .cat-browse { margin-top: 1rem; display: inline-flex; align-items: center; gap: .25rem; color: var(--tx); font-size: .875rem; font-weight: 600; }
        .cat-arrow { transition: transform .2s ease; }
        .cat-card:hover .cat-arrow { transform: translateX(3px); }

        /* "How it works" step cards: brand orange. */
        .step-card {
            --c: #F16029; --bg: #FEF0EA; --bd: #FBD4C4; --tx: #B8461A;
            position: relative; overflow: hidden; padding: 1.25rem; border-radius: 1rem;
            border: 1px solid var(--bd);
            background: linear-gradient(160deg, var(--bg) 0%, #ffffff 75%);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .step-card::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 4px; background: var(--c); }
        .step-card:hover { transform: translateY(-3px); border-color: var(--c); box-shadow: 0 14px 28px -16px var(--c); }
        .step-num { display: inline-flex; width: 2.9rem; height: 2.9rem; align-items: center; justify-content: center; border-radius: .8rem; background: var(--c); color: #fff; font-weight: 700; font-size: 1.15rem; box-shadow: 0 8px 16px -8px var(--c); }
        .step-card:hover .step-title { color: var(--tx); }

        /* Impact numbers: neutral white cards on a light-grey band, no colour accents. */
        .stat-card {
            padding: 1.5rem 1.25rem; text-align: center; border-radius: 1rem;
            background: #ffffff; border: 1px solid #E2E8F0;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .stat-card:hover { transform: translateY(-3px); border-color: #CBD5E1; box-shadow: 0 14px 28px -18px rgba(15, 23, 42, .35); }
        .stat-num { font-weight: 700; font-size: 2.25rem; line-height: 1.1; color: #1F2937; }
        .stat-label { margin-top: .35rem; font-size: .875rem; font-weight: 600; color: #475569; }
    </style>
@endpush

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
                        {{ __('Build Digital Skills.') }}<br class="hidden sm:block">
                        <span class="text-accent">{{ __('Grow Your Impact.') }}</span>
                    </h1>
                    <p class="mt-6 text-base sm:text-lg text-slate-300 leading-relaxed max-w-xl">
                        {{ __('The official learning platform for KoraLink Agents under the Digital Jobs for Youth in Health program. Develop practical digital, marketplace, and professional skills to serve your community with confidence.') }}
                    </p>
                    <div class="mt-9 flex flex-wrap gap-3">
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition shadow-brand-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-navy">
                            {{ __('Explore Courses') }}
                        </a>
                        <a href="{{ route('about') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-navy">
                            {{ __('Learn About KoraLink') }}
                        </a>
                    </div>
                </div>

                <div class="lg:col-span-6">
                    <div class="relative max-w-lg mx-auto lg:max-w-none lg:ml-auto">
                        <div class="relative aspect-[4/3] rounded-2xl overflow-hidden shadow-brand ring-1 ring-white/10">
                            <img
                                src="{{ asset('images/banner/agents-workshop.jpg') }}"
                                alt="{{ __('KoraLink agents learning together in a training workshop') }}"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                fetchpriority="high"
                            >
                        </div>
                        <div class="absolute -bottom-6 -left-2 sm:left-4 w-[48%] sm:w-[44%] aspect-[4/3] rounded-xl overflow-hidden shadow-xl ring-2 ring-navy">
                            <img
                                src="{{ asset('images/banner/agents-training.jpg') }}"
                                alt="{{ __('KoraLink agents during a hands-on training session') }}"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="lazy"
                            >
                        </div>
                        <div class="absolute -top-3 right-2 sm:right-6 rounded-xl bg-white text-navy px-3.5 py-2.5 shadow-lg max-w-[11rem]">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-primary">{{ __('Youth in Health') }}</p>
                            <p class="mt-0.5 text-xs text-slate-600 leading-snug">{{ __('Digital skills for community impact') }}</p>
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
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('Browse by category') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('Find learning paths that match the skills you need to grow.') }}</p>
            </div>

            @if($categories->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($categories as $category)
                        <a
                            href="{{ route('courses.index', ['category' => $category->slug]) }}"
                            class="cat-card group"
                            style="{{ \App\Support\CategoryTheme::style() }}"
                        >
                            <span class="cat-icon">
                                @include('partials.category-icon', ['slug' => $category->slug, 'name' => $category->name, 'class' => 'h-5 w-5'])
                            </span>
                            <span class="cat-name font-display">{{ $category->tr('name') }}</span>
                            <span class="cat-count">
                                {{ trans_choice('{1} :count course|[2,*] :count courses', $category->courses_count) }}
                            </span>
                            <span class="cat-browse">
                                {{ __('Browse') }}
                                <svg class="cat-arrow h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('Categories will appear here once they are added.') }}</p>
            @endif
        </div>
    </section>

    {{-- Featured courses --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div class="max-w-2xl">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('Start Learning Today') }}</h2>
                    <p class="mt-2 text-slate-600">{{ __('Explore practical courses designed to help you build skills and grow your impact.') }}</p>
                </div>
                <a href="{{ route('courses.index') }}" class="text-primary hover:text-accent-dark font-semibold inline-flex items-center gap-1 shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                    {{ __('View All Courses') }}
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            @if($latestCourses->isNotEmpty())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    @foreach($latestCourses as $course)
                        @php
                            $lessonCount = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
                        @endphp
                        <article data-reveal class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary-muted hover:shadow-brand transition">
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
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-[11px] font-semibold uppercase tracking-wide">{{ $course->category->tr('name') }}</span>
                                    @endif
                                    @if(($course->price ?? 0) > 0)
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-accent-light text-accent-darker text-[11px] font-semibold">${{ number_format($course->price, 0) }}</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-[11px] font-semibold">{{ __('Free') }}</span>
                                    @endif
                                    @include('courses.partials.access-badge')
                                </div>
                                <h3 class="mt-3 font-display font-semibold text-lg text-navy leading-snug">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        {{ $course->tr('title') }}
                                    </a>
                                </h3>
                                @if(filled($course->tr('description')))
                                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit(trim(preg_replace('/\s+/', ' ', $course->tr('description'))), 110) }}</p>
                                @endif
                                <div class="mt-auto pt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                    @if($course->instructor)
                                        <span>{{ $course->instructor->tr('name') }}</span>
                                    @endif
                                    @if(filled($course->tr('duration')))
                                        <span>{{ $course->tr('duration') }}</span>
                                    @endif
                                    @if($lessonCount > 0)
                                        <span>{{ trans_choice('{1} :count lesson|[2,*] :count lessons', $lessonCount) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('courses.show', $course) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
                                    {{ __('View course') }}
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500">{{ __('Courses will appear here once they are published.') }}</p>
            @endif
        </div>
    </section>

    {{-- How it works --}}
    <section class="py-16 lg:py-20 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('How KoraLink Academy Works') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('A clear path from registration to completed learning.') }}</p>
            </div>
            <ol class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach([
                    ['01', __('Create Your Account'), __('Register and access your learner profile.')],
                    ['02', __('Explore Courses'), __('Browse by category and find the skills you need.')],
                    ['03', __('Learn at Your Pace'), __('Access lessons and track your progress as you go.')],
                    ['04', __('Complete & Grow'), __('Finish your journey and keep building new skills.')],
                ] as [$step, $title, $copy])
                    <li class="step-card">
                        <span class="step-num font-display tabular-nums">{{ $step }}</span>
                        <h3 class="step-title mt-4 font-display font-semibold text-navy transition">{{ $title }}</h3>
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
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent">{{ __('Program') }}</p>
                    <h2 class="mt-3 font-display font-bold text-2xl lg:text-3xl text-navy">KoraLink Agents Academy</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        {{ __('The official learning platform supporting Digital Jobs for Youth in Health and the development of Digital Community Champions.') }}
                    </p>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        {{ __('The Academy provides practical learning resources that help agents strengthen digital skills, use digital marketplaces effectively, deliver better services, and contribute to stronger communities.') }}
                    </p>
                    <a href="{{ route('about') }}" class="mt-8 inline-flex items-center px-6 py-3 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        {{ __('Learn More About KoraLink') }}
                    </a>
                </div>
                <div class="lg:col-span-7 order-1 lg:order-2">
                    <div class="grid grid-cols-12 gap-3 sm:gap-4">
                        <div class="col-span-7 relative aspect-[4/5] rounded-2xl overflow-hidden">
                            <img src="{{ asset('images/banner/agents-field.jpg') }}" alt="{{ __('KoraLink agents working in the community') }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                        </div>
                        <div class="col-span-5 flex flex-col gap-3 sm:gap-4">
                            <div class="relative flex-1 min-h-[7rem] rounded-2xl overflow-hidden">
                                <img src="{{ asset('images/banner/agents-workshop.jpg') }}" alt="{{ __('Agents collaborating in a workshop') }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            </div>
                            <div class="relative flex-1 min-h-[7rem] rounded-2xl overflow-hidden">
                                <img src="{{ asset('images/banner/agents-training.jpg') }}" alt="{{ __('Agents in a training session') }}" class="absolute inset-0 w-full h-full object-cover" loading="lazy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust / impact --}}
    <section class="py-14 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $hasStats = ($stats['learners'] ?? 0) > 0 || ($stats['courses'] ?? 0) > 0 || ($stats['categories'] ?? 0) > 0;
            @endphp
            @if($hasStats)
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    @if(($stats['learners'] ?? 0) > 0)
                        <div class="stat-card">
                            <p class="stat-num font-display tabular-nums">{{ number_format($stats['learners']) }}</p>
                            <p class="stat-label">{{ __('Learners') }}</p>
                        </div>
                    @endif
                    @if(($stats['courses'] ?? 0) > 0)
                        <div class="stat-card">
                            <p class="stat-num font-display tabular-nums">{{ number_format($stats['courses']) }}</p>
                            <p class="stat-label">{{ __('Courses') }}</p>
                        </div>
                    @endif
                    @if(($stats['categories'] ?? 0) > 0)
                        <div class="stat-card">
                            <p class="stat-num font-display tabular-nums">{{ number_format($stats['categories']) }}</p>
                            <p class="stat-label">{{ __('Categories') }}</p>
                        </div>
                    @endif
                    @if(($stats['enrollments'] ?? 0) > 0)
                        <div class="stat-card">
                            <p class="stat-num font-display tabular-nums">{{ number_format($stats['enrollments']) }}</p>
                            <p class="stat-label">{{ __('Enrollments') }}</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach([
                        __('Practical digital learning'),
                        __('Community-focused training'),
                        __('Skills for the digital economy'),
                        __('Supported by program partners'),
                    ] as $statement)
                        <div class="rounded-2xl bg-white border border-slate-200 px-5 py-5">
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
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('Our Partners') }}</h2>
                <p class="mt-2 text-slate-600">{{ __('Working together to strengthen digital skills, innovation, and community impact.') }}</p>
            </div>

            @if($partners->isNotEmpty())
                <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
                    @foreach($partners as $partner)
                        <li data-reveal class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-6 min-h-[7.5rem]">
                            @if($partner->logo_url)
                                <img
                                    src="{{ $partner->logo_url }}"
                                    alt="{{ $partner->name ?: __('Partner organization') }}"
                                    class="w-auto max-w-full object-contain"
                                    style="height: 80px;"
                                    loading="lazy"
                                >
                            @endif
                            @if(filled($partner->name))
                                <span class="text-xs sm:text-sm font-medium text-slate-600 text-center">{{ $partner->name }}</span>
                            @elseif(! $partner->logo_url)
                                <span class="text-sm text-slate-400">{{ __('Partner') }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-center text-sm text-slate-500">{{ __('Partner logos will appear here once they are added.') }}</p>
            @endif
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-16 lg:py-20 bg-navy text-white">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display font-bold text-2xl sm:text-3xl lg:text-4xl tracking-tight">{{ __('Ready to grow your digital skills?') }}</h2>
            <p class="mt-4 text-slate-300 leading-relaxed">
                {{ __('Explore practical courses designed to help you learn, improve your work, and create greater impact in your community.') }}
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    {{ __('Explore Courses') }}
                </a>
                <a href="{{ route('about') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                    {{ __('About KoraLink') }}
                </a>
            </div>
        </div>
    </section>

@push('scripts')
    <script>
        (function () {
            window.__revealReady = true;
            var root = document.documentElement;
            var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            // Respect "reduce motion", and browsers without IntersectionObserver: show everything, no animation.
            if (reduce || !('IntersectionObserver' in window)) {
                root.classList.remove('js-reveal');
                return;
            }

            // Numbers count up from 0 when their card appears. The real value stays in the HTML as a fallback.
            document.querySelectorAll('.stat-num').forEach(function (n) {
                var value = parseInt(n.textContent.replace(/[^0-9]/g, ''), 10);
                if (!isNaN(value)) { n.dataset.target = value; n.textContent = '0'; }
            });

            function countUp(card) {
                var n = card.querySelector('.stat-num');
                if (!n || n.dataset.target === undefined) return;
                var target = parseInt(n.dataset.target, 10), start = null, duration = 1300;
                function step(ts) {
                    if (start === null) start = ts;
                    var p = Math.min((ts - start) / duration, 1);
                    n.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
                    if (p < 1) requestAnimationFrame(step); else n.textContent = target.toLocaleString('en-US');
                }
                requestAnimationFrame(step);
                // Safety: always land on the real number, even if the tab was in the background mid-count.
                setTimeout(function () { n.textContent = target.toLocaleString('en-US'); }, duration + 200);
            }

            var items = document.querySelectorAll('.cat-card, .step-card, .stat-card, [data-reveal]');
            items.forEach(function (el) {
                var index = Array.prototype.indexOf.call(el.parentNode.children, el);
                el.style.setProperty('--d', Math.min(index, 6) * 90 + 'ms');   // stagger within a row/grid
                el.classList.add('reveal');
            });

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    var el = entry.target;
                    observer.unobserve(el);
                    el.classList.add('is-visible');
                    countUp(el);
                    // Hand the element back to its normal styles (so hover effects work again).
                    var done = function () { el.classList.remove('reveal', 'is-visible'); el.style.removeProperty('--d'); };
                    el.addEventListener('transitionend', function onEnd(ev) {
                        if (ev.propertyName === 'opacity') { el.removeEventListener('transitionend', onEnd); done(); }
                    });
                    setTimeout(done, 1800);
                });
            }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

            items.forEach(function (el) { observer.observe(el); });
        })();
    </script>
@endpush
@endsection
