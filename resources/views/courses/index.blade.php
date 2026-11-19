@extends('layouts.site')

@section('title', $activeCategory ? __(':category Courses', ['category' => $activeCategory->tr('name')]) : __('All Courses'))
@section('body_class', 'has-banner')

@section('content')
    <section class="page-banner">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav class="text-sm text-slate-300 mb-4" aria-label="{{ __('Breadcrumb') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">{{ __('Home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">{{ __('Courses') }}</li>
                    @if($activeCategory)
                        <li aria-hidden="true">/</li>
                        <li class="text-white font-medium">{{ $activeCategory->tr('name') }}</li>
                    @endif
                </ol>
            </nav>
            <h1 class="font-display font-bold text-3xl sm:text-4xl tracking-tight">
                {{ $activeCategory ? $activeCategory->tr('name') : __('All Courses') }}
            </h1>
            <p class="mt-3 max-w-2xl text-slate-300 leading-relaxed">
                @if($activeCategory)
                    {{ __('Practical courses in :category to help you build skills and grow your impact.', ['category' => $activeCategory->tr('name')]) }}
                @else
                    {{ __('Browse the full catalog and find practical learning for your work as a Digital Community Champion.') }}
                @endif
            </p>
            @if($courses->total() > 0)
                <p class="mt-5 text-sm text-slate-400">
                    {{ trans_choice('{1} :count course|[2,*] :count courses', $courses->total(), ['count' => number_format($courses->total())]) }}
                    @if($activeCategory)
                        {{ __('in this category') }}
                    @endif
                </p>
            @endif
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($categories->isNotEmpty())
                <div class="mb-8 lg:mb-10" id="categories">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500 mb-3">{{ __('Filter by category') }}</p>
                    <div class="flex flex-wrap gap-2">
                        <a
                            href="{{ route('courses.index') }}"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-medium border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 {{ ! $activeCategory ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 border-slate-200 hover:border-primary-muted hover:text-primary' }}"
                        >
                            {{ __('All courses') }}
                        </a>
                        @foreach($categories as $cat)
                            <a
                                href="{{ route('courses.index', ['category' => $cat->slug]) }}"
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-sm font-medium border transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 {{ ($activeCategory?->slug ?? null) === $cat->slug ? 'bg-primary text-white border-primary' : 'bg-white text-slate-700 border-slate-200 hover:border-primary-muted hover:text-primary' }}"
                            >
                                <span class="{{ ($activeCategory?->slug ?? null) === $cat->slug ? 'text-white' : 'text-primary' }}">
                                    @include('partials.category-icon', ['slug' => $cat->slug, 'name' => $cat->tr('name'), 'class' => 'h-4 w-4'])
                                </span>
                                {{ $cat->tr('name') }}
                                <span class="tabular-nums opacity-70">{{ $cat->courses_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($courses->isEmpty())
                <div class="text-center py-16 px-6 rounded-2xl bg-white border border-slate-200">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-light text-primary mb-4">
                        @include('partials.category-icon', ['slug' => $activeCategory->slug ?? 'book', 'name' => $activeCategory->name ?? '', 'class' => 'h-6 w-6'])
                    </div>
                    <h2 class="font-display font-semibold text-lg text-navy">{{ __('No courses found') }}</h2>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                        @if($activeCategory)
                            {{ __('There are no courses in :category yet. Try another category or browse the full catalog.', ['category' => $activeCategory->tr('name')]) }}
                        @else
                            {{ __('Check back soon — new courses are added regularly.') }}
                        @endif
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        @if($activeCategory)
                            <a href="{{ route('courses.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">{{ __('View all courses') }}</a>
                        @endif
                        <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl border border-slate-300 text-navy font-medium hover:bg-slate-50 transition">{{ __('Back to home') }}</a>
                    </div>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    @foreach($courses as $course)
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
                                        @include('partials.category-icon', ['slug' => $course->category->slug ?? '', 'name' => $course->category?->tr('name') ?? '', 'class' => 'h-10 w-10'])
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
                                <h2 class="mt-3 font-display font-semibold text-lg text-navy leading-snug">
                                    <a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        {{ $course->tr('title') }}
                                    </a>
                                </h2>
                                @if(filled($course->description))
                                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit(trim(preg_replace('/\s+/', ' ', $course->tr('description'))), 110) }}</p>
                                @endif
                                <div class="mt-auto pt-4 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                    @if($course->instructor)
                                        <span>{{ $course->instructor->tr('name') }}</span>
                                    @endif
                                    @if(filled($course->duration))
                                        <span>{{ $course->tr('duration') }}</span>
                                    @else
                                        <span>{{ __('Self-paced') }}</span>
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

                @if($courses->hasPages())
                    <div class="mt-12">
                        {{ $courses->withQueryString()->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
