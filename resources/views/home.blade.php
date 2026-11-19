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

        /* ---- Home hero: floating pill menu, brand-blue banner, circular photo, curved bottom ---- */
        .home-hero { position: relative; overflow: hidden; color: #fff; background: linear-gradient(100deg, #102C5E 0%, #153F86 46%, #19499B 100%); }
        .home-hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(circle at 88% 30%, rgba(255, 255, 255, .10) 0, transparent 46%), radial-gradient(circle at 8% 90%, rgba(241, 96, 41, .16) 0, transparent 38%); pointer-events: none; }
        .home-hero-inner { position: relative; padding-top: 8.75rem; padding-bottom: 7.5rem; }
        .home-hero-grid { display: grid; gap: 3rem; align-items: center; }
        @media (min-width: 1024px) { .home-hero-grid { grid-template-columns: 1.08fr .92fr; gap: 3.5rem; } .home-hero-inner { padding-top: 9.5rem; padding-bottom: 9rem; } }
        .home-eyebrow { font-size: .8rem; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: #F16029; }
        .home-hero h1 { margin-top: 1rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.08; font-size: clamp(2.35rem, 5.4vw, 3.6rem); color: #fff; }
        .home-hero-hl { color: #F16029; display: inline-block; }
        .home-hero-lead { margin-top: 1.5rem; max-width: 36rem; font-size: 1.05rem; line-height: 1.7; color: rgba(255, 255, 255, .88); }
        .home-hero-actions { margin-top: 2.25rem; display: flex; flex-wrap: wrap; gap: .85rem; }
        .home-btn { display: inline-flex; align-items: center; justify-content: center; padding: .85rem 1.7rem; border-radius: 999px; font-weight: 600; transition: transform .2s ease, background-color .2s ease, box-shadow .2s ease; }
        .home-btn:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
        .home-btn--primary { background: #F16029; color: #fff; box-shadow: 0 12px 26px -12px rgba(241, 96, 41, .9); }
        .home-btn--primary:hover { background: #D95420; transform: translateY(-2px); }
        .home-btn--ghost { color: #fff; border: 1.5px solid rgba(255, 255, 255, .45); }
        .home-btn--ghost:hover { background: rgba(255, 255, 255, .12); transform: translateY(-2px); }
        .home-hero-art { position: relative; isolation: isolate; width: min(100%, 29rem); margin: 0 auto; }
        .home-hero-photo { position: relative; aspect-ratio: 1; border-radius: 50%; border: 5px solid #F16029; padding: 10px; background: #fff; box-shadow: 0 30px 60px -26px rgba(0, 0, 0, .55); }
        .home-hero-slides { position: relative; width: 100%; height: 100%; overflow: hidden; border-radius: 50%; background: #102C5E; }
        .home-hero-slide { position: absolute; inset: 0; width: 100%; height: 100%; display: block; object-fit: cover; opacity: 0; transform: scale(1.07); transition: opacity 1s ease, transform 6.5s ease-out; }
        .home-hero-slide.is-active { opacity: 1; transform: scale(1); }
        .home-hero-dots { display: none; justify-content: center; gap: .5rem; margin-top: 1.4rem; }
        .home-hero-art.is-slideshow .home-hero-dots { display: flex; }
        .home-hero-dot { width: .6rem; height: .6rem; padding: 0; border: 0; border-radius: 999px; background: rgba(255, 255, 255, .45); cursor: pointer; transition: width .3s ease, background-color .3s ease; }
        .home-hero-dot:hover { background: rgba(255, 255, 255, .8); }
        .home-hero-dot.is-active { width: 1.7rem; background: #F16029; }
        .home-hero-dot:focus-visible { outline: 2px solid #fff; outline-offset: 3px; }
        @media (prefers-reduced-motion: reduce) { .home-hero-slide { transition: opacity .01s; transform: none; } .home-hero-dot { transition: none; } }
        .home-hero-blob { position: absolute; z-index: -1; right: -7%; bottom: -5%; width: 36%; aspect-ratio: 1; border-radius: 50%; background: #F16029; }
        .home-hero-ring { position: absolute; z-index: -1; left: -9%; top: 6%; width: 22%; aspect-ratio: 1; border-radius: 50%; border: 2px solid rgba(201, 214, 238, .55); }
        .home-hero-chip { position: absolute; left: -4%; bottom: 12%; max-width: 12.5rem; padding: .7rem .9rem; border-radius: .9rem; background: #fff; color: #0B1B33; box-shadow: 0 16px 30px -14px rgba(0, 0, 0, .5); }
        .home-hero-chip strong { display: block; font-size: .68rem; letter-spacing: .08em; text-transform: uppercase; color: #19499B; }
        .home-hero-chip span { display: block; margin-top: .15rem; font-size: .78rem; line-height: 1.3; color: #475569; }
        .home-hero-doodle { position: absolute; pointer-events: none; }
        @media (max-width: 1023px) { .home-hero-doodle--arrow { display: none; } .home-hero-doodle:not(.home-hero-doodle--arrow) { width: 3.5rem !important; right: 4% !important; top: 6rem !important; } }
        .home-hero-curve { position: absolute; left: 0; bottom: -1px; width: 100%; height: clamp(3.5rem, 9vw, 7rem); display: block; }
        @media (max-width: 1023px) { .home-hero-art { width: min(78%, 24rem); } .home-hero-chip { left: -2%; } }
        @media (prefers-reduced-motion: no-preference) { .home-hero-art .home-hero-photo { animation: heroFloat 7s ease-in-out infinite; } }
        @keyframes heroFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

        /* ---- Call-to-action card (end of the page) ---- */
        .home-cta { background: #F8FAFC; padding: .5rem 0 4.5rem; }
        .home-cta-card { position: relative; display: grid; gap: 1.5rem; overflow: hidden; border-radius: 2rem; color: #fff; padding: 2.25rem 1.5rem 0; background: linear-gradient(100deg, #102C5E 0%, #153F86 55%, #19499B 100%); box-shadow: 0 34px 60px -34px rgba(16, 44, 94, .7); }
        .home-cta h2 { font-weight: 700; line-height: 1.1; letter-spacing: -.02em; font-size: clamp(1.9rem, 4.4vw, 2.85rem); }
        .home-cta-hl { color: #F16029; }
        .home-cta-lead { margin-top: 1rem; max-width: 30rem; line-height: 1.65; color: rgba(255, 255, 255, .86); }
        .home-cta-actions { margin-top: 1.75rem; display: flex; flex-wrap: wrap; gap: .8rem; }
        .home-btn--white { background: #fff; color: #153F86; box-shadow: 0 12px 26px -14px rgba(0, 0, 0, .6); }
        .home-btn--white:hover { background: #E8EEF7; transform: translateY(-2px); }
        .home-cta-art { position: relative; min-height: 15rem; }
        .home-cta-bar { position: absolute; background: #F16029; border-radius: 1.1rem 0 0 1.1rem; }
        .home-cta-bar--a { right: -1.5rem; top: 14%; width: 62%; height: 26%; }
        .home-cta-bar--b { right: -1.5rem; bottom: 12%; width: 52%; height: 26%; }
        .home-cta-photo { position: absolute; left: 6%; bottom: 0; width: 70%; height: 92%; object-fit: cover; object-position: 50% 28%; border-radius: 1.6rem 1.6rem 0 0; box-shadow: 0 20px 40px -20px rgba(0, 0, 0, .55); }
        .home-cta-badge { position: absolute; right: .25rem; top: .25rem; width: 3rem; height: 3rem; display: grid; place-items: center; border-radius: 50%; background: #fff; box-shadow: 0 12px 24px -12px rgba(0, 0, 0, .55); }
        @media (min-width: 900px) {
            .home-cta-card { grid-template-columns: 1.05fr .95fr; align-items: center; padding: 3.25rem 0 0 3.5rem; min-height: 26.5rem; }
            .home-cta-copy { padding-bottom: 3.25rem; }
            .home-cta-art { align-self: stretch; min-height: 24rem; }
            .home-cta-badge { right: 2rem; top: 1.75rem; width: 3.25rem; height: 3.25rem; }
        }
    </style>
@endpush

@section('body_class', 'has-banner home-page')
@section('title', __('Home'))

@section('content')
    {{-- Hero --}}
    <section class="home-hero">
        {{-- decorative doodles (purely visual) --}}
        <svg class="home-hero-doodle home-hero-doodle--arrow" style="left: 44%; top: 6.6rem; width: 8rem; opacity: .8;" viewBox="0 0 140 90" fill="none" aria-hidden="true">
            <path d="M6 82 C 30 20, 80 8, 118 30" stroke="#C9D6EE" stroke-width="2" stroke-dasharray="5 6" stroke-linecap="round"/>
            <path d="M104 14 L124 32 L99 38 Z" fill="#7FB2F0"/>
        </svg>
        <svg class="home-hero-doodle" style="right: 6%; top: 7rem; width: 5.5rem;" viewBox="0 0 90 90" fill="none" aria-hidden="true">
            <path d="M45 46 c0-6 8-6 8 0 c0 10-16 10-16-2 c0-16 26-16 26 2 c0 24-40 24-40-4 c0-30 56-28 56 6" stroke="#F16029" stroke-width="4" stroke-linecap="round"/>
            <path d="M70 66 l-1 14 l12-8" stroke="#F16029" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <div class="home-hero-inner max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="home-hero-grid">
                <div>
                    <p class="home-eyebrow">{{ __('KoraLink Agents Academy') }}</p>
                    <h1 class="font-display">
                        {{ __('Build Digital Skills.') }}
                        <span class="home-hero-hl">{{ __('Grow Your Impact.') }}</span>
                    </h1>
                    <p class="home-hero-lead">
                        {{ __('The official learning platform for KoraLink Agents under the Digital Jobs for Youth in Health program. Develop practical digital, marketplace, and professional skills to serve your community with confidence.') }}
                    </p>
                    <div class="home-hero-actions">
                        <a href="{{ route('courses.index') }}" class="home-btn home-btn--primary">{{ __('Explore Courses') }}</a>
                        <a href="{{ route('about') }}" class="home-btn home-btn--ghost">{{ __('Learn About KoraLink') }}</a>
                    </div>
                </div>

                <div class="home-hero-art">
                    <span class="home-hero-ring" aria-hidden="true"></span>
                    <span class="home-hero-blob" aria-hidden="true"></span>
                    @php
                        $heroSlides = [
                            ['file' => 'agents-field.jpg', 'alt' => __('KoraLink agents checking a phone together in the field'), 'pos' => '50% 32%'],
                            ['file' => 'agents-workshop.jpg', 'alt' => __('KoraLink agents learning together in a training workshop'), 'pos' => '50% 40%'],
                            ['file' => 'agents-training.jpg', 'alt' => __('KoraLink agents during a hands-on training session'), 'pos' => '50% 62%'],
                        ];
                    @endphp
                    <div class="home-hero-photo">
                        <div class="home-hero-slides" data-hero-slides>
                            @foreach($heroSlides as $i => $slide)
                                <img
                                    class="home-hero-slide{{ $i === 0 ? ' is-active' : '' }}"
                                    src="{{ asset('images/banner/'.$slide['file']) }}"
                                    alt="{{ $slide['alt'] }}"
                                    style="object-position: {{ $slide['pos'] }};"
                                    width="1024" height="682"
                                    @if($i === 0) fetchpriority="high" @endif
                                >
                            @endforeach
                        </div>
                    </div>
                    <div class="home-hero-dots" role="group" aria-label="{{ __('Banner photos') }}">
                        @foreach($heroSlides as $i => $slide)
                            <button type="button" class="home-hero-dot{{ $i === 0 ? ' is-active' : '' }}" aria-label="{{ __('Show photo :number', ['number' => $i + 1]) }}" @if($i === 0) aria-current="true" @endif></button>
                        @endforeach
                    </div>
                    <div class="home-hero-chip">
                        <strong>{{ __('Youth in Health') }}</strong>
                        <span>{{ __('Digital skills for community impact') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- curved bottom edge, in the colour of the next section --}}
        <svg class="home-hero-curve" viewBox="0 0 1440 100" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0,0 C480,100 960,100 1440,0 L1440,100 L0,100 Z" fill="#F8FAFC"/>
        </svg>
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

    {{-- Call to action (under the categories) --}}
    <section class="home-cta">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="home-cta-card" data-reveal>
                <div class="home-cta-copy">
                    <h2 class="font-display" aria-label="{{ __('Ready to grow your digital skills?') }}">
                        {{ __('Ready to grow') }}
                        <span class="home-cta-hl">{{ __('your digital skills?') }}</span>
                    </h2>
                    <p class="home-cta-lead">
                        {{ __('Explore practical courses designed to help you learn, improve your work, and create greater impact in your community.') }}
                    </p>
                    <div class="home-cta-actions">
                        <a href="{{ route('contact') }}" class="home-btn home-btn--white">{{ __('Contact us') }}</a>
                    </div>
                </div>

                <div class="home-cta-art" aria-hidden="false">
                    <span class="home-cta-bar home-cta-bar--a" aria-hidden="true"></span>
                    <span class="home-cta-bar home-cta-bar--b" aria-hidden="true"></span>
                    <img
                        class="home-cta-photo"
                        src="{{ asset('images/banner/agents-workshop.jpg') }}"
                        alt="{{ __('KoraLink agents learning together in a training workshop') }}"
                        width="1024" height="682"
                        loading="lazy"
                    >
                    <span class="home-cta-badge" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#F16029" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="9" r="5.2"/>
                            <path d="M8.6 13.3 7 21l5-2.6L17 21l-1.6-7.7"/>
                            <path d="m10 9 1.5 1.5L14.3 7.7"/>
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured courses --}}
    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-10">
                <div class="max-w-2xl">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('Start Learning Today') }}</h2>
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
                    <h2 class="mt-3 font-display font-bold text-2xl lg:text-3xl text-navy">{{ __('KoraLink Agents Academy') }}</h2>
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
    <script>
            // ---- Hero photos: rotate with a fade, dots to choose, paused on hover/focus, no autoplay with reduced motion ----
            (function () {
                var root = document.querySelector('[data-hero-slides]');
                if (!root) return;
                var art = root.closest('.home-hero-art');
                var slides = Array.prototype.slice.call(root.querySelectorAll('.home-hero-slide'));
                var dots = art ? Array.prototype.slice.call(art.querySelectorAll('.home-hero-dot')) : [];
                if (slides.length < 2) return;
                art.classList.add('is-slideshow');                       // shows the dots (they do nothing without JavaScript)

                var current = 0, timer = null, paused = false;
                var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function show(n) {
                    n = (n + slides.length) % slides.length;
                    slides[current].classList.remove('is-active');
                    if (dots[current]) { dots[current].classList.remove('is-active'); dots[current].removeAttribute('aria-current'); }
                    current = n;
                    slides[current].classList.add('is-active');
                    if (dots[current]) { dots[current].classList.add('is-active'); dots[current].setAttribute('aria-current', 'true'); }
                }
                function start() {
                    if (reduce) return;                                  // people who asked for less motion choose photos with the dots
                    clearInterval(timer);
                    timer = setInterval(function () { if (!paused && !document.hidden) show(current + 1); }, 5500);
                }

                dots.forEach(function (dot, n) { dot.addEventListener('click', function () { show(n); start(); }); });
                ['mouseenter', 'focusin'].forEach(function (e) { art.addEventListener(e, function () { paused = true; }); });
                ['mouseleave', 'focusout'].forEach(function (e) { art.addEventListener(e, function () { paused = false; }); });
                start();
            })();
    </script>
@endpush
@endsection
