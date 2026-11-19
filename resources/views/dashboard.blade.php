@extends('layouts.site')

@section('title', __('My Learning'))
@section('body_class', 'has-banner')

@section('content')
    @php
        $displayName = trim((auth()->user()->displayFirstName() ?: '').' '.(auth()->user()->displayLastName() ?: ''))
            ?: auth()->user()->email;
        $firstName = auth()->user()->displayFirstName() ?: $displayName;
    @endphp

    <section class="page-banner">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav class="text-sm text-slate-300 mb-4" aria-label="{{ __('Breadcrumb') }}">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">{{ __('Home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">{{ __('My Learning') }}</li>
                </ol>
            </nav>
            <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.18em] text-accent">{{ __('Welcome back') }}</p>
            <h1 class="mt-3 font-display font-bold text-3xl sm:text-4xl tracking-tight">
                {{ $firstName }}
            </h1>
            <p class="mt-3 max-w-2xl text-slate-300 leading-relaxed">
                {{ __('Continue your courses, track progress, and explore new learning paths.') }}
            </p>
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('courses.my-courses') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </span>
                    <h2 class="mt-4 font-display font-semibold text-navy group-hover:text-primary transition">{{ __('My courses') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ trans_choice('{1} :count recent enrollment|[2,*] :count recent enrollments', $courseEnrollments->count()) }}</p>
                </a>
                <a href="{{ route('bundles.my-bundles') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <h2 class="mt-4 font-display font-semibold text-navy group-hover:text-primary transition">{{ __('My bundles') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ trans_choice('{1} :count path|[2,*] :count paths', $bundleEnrollments->count()) }}</p>
                </a>
                <a href="{{ route('courses.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-accent-light text-accent-darker">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <h2 class="mt-4 font-display font-semibold text-navy group-hover:text-primary transition">{{ __('Browse courses') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Find something new to learn') }}</p>
                </a>
                <a href="{{ route('profile.edit') }}" class="group rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                    <h2 class="mt-4 font-display font-semibold text-navy group-hover:text-primary transition">{{ __('Profile') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('Update your account details') }}</p>
                </a>
            </div>

            @php
                $hasOrgCourses = $organizationCourses->isNotEmpty();
                $hasAssigned = $availableCourses->isNotEmpty() || $hasOrgCourses;
                $continue = $hasAssigned ? $otherEnrollments : $courseEnrollments;
            @endphp

            @if($hasOrgCourses)
                <div>
                    <div class="mb-5">
                        <h2 class="font-display font-bold text-xl text-navy">{{ __(':organization courses', ['organization' => $organization->name]) }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Selected for your organization') }}</p>
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($organizationCourses as $course)
                            @include('courses.partials.dashboard-card', ['course' => $course, 'enrolled' => $enrolledCourseIds->contains($course->id)])
                        @endforeach
                    </div>
                </div>
            @endif

            @if($availableCourses->isNotEmpty())
                <div>
                    <div class="mb-5">
                        <h2 class="font-display font-bold text-xl text-navy">{{ $hasOrgCourses ? __('Other courses') : __('Courses for you') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $hasOrgCourses ? __('More courses available to your account') : __('Available to your account (:category), starting with the ones made for you', ['category' => __(auth()->user()->learnerCategoryLabel())]) }}
                        </p>
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($availableCourses as $course)
                            @include('courses.partials.dashboard-card', ['course' => $course, 'enrolled' => $enrolledCourseIds->contains($course->id)])
                        @endforeach
                    </div>
                </div>
            @endif

            @if(! $hasAssigned || $continue->isNotEmpty())
            <div>
                <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
                    <div>
                        <h2 class="font-display font-bold text-xl text-navy">{{ __('Continue learning') }}</h2>
                        <p class="mt-1 text-sm text-slate-500">{{ __('Pick up where you left off') }}</p>
                    </div>
                    @if($continue->isNotEmpty())
                        <a href="{{ route('courses.my-courses') }}" class="text-sm font-semibold text-primary hover:text-accent-dark">{{ __('View all courses →') }}</a>
                    @endif
                </div>

                @if($continue->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center">
                        <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-light text-primary mb-4">
                            @include('partials.category-icon', ['slug' => 'book', 'name' => '', 'class' => 'h-6 w-6'])
                        </div>
                        <h3 class="font-display font-semibold text-lg text-navy">{{ __('No courses yet') }}</h3>
                        <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">{{ __('Enroll in a course to start tracking your progress here.') }}</p>
                        <a href="{{ route('courses.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">{{ __('Browse courses') }}</a>
                    </div>
                @else
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($continue as $enrollment)
                            @php
                                $course = $enrollment->course;
                                $total = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
                                $completed = auth()->user()->completedLessonsCountForCourse($course);
                                $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
                                $actionLabel = $pct >= 100 ? __('View') : ($completed > 0 ? __('Resume') : __('Start'));
                            @endphp
                            <article class="group flex flex-col rounded-2xl border border-slate-200 bg-white overflow-hidden hover:border-primary-muted hover:shadow-brand transition">
                                <a href="{{ route('courses.show', $course) }}" class="block aspect-[16/10] bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary">
                                    @if($course->banner_url)
                                        <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-light to-slate-100 text-primary">
                                            @include('partials.category-icon', [
                                                'slug' => $course->category->slug ?? 'book',
                                                'name' => $course->category->name ?? '',
                                                'class' => 'h-10 w-10',
                                            ])
                                        </div>
                                    @endif
                                </a>
                                <div class="flex flex-1 flex-col p-5">
                                    @if($course->category)
                                        <span class="inline-flex self-start px-2 py-0.5 rounded-md bg-primary-light text-primary text-[11px] font-semibold uppercase tracking-wide">{{ $course->category->tr('name') }}</span>
                                    @endif
                                    <h3 class="mt-2 font-display font-semibold text-navy leading-snug">
                                        <a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition">{{ $course->tr('title') }}</a>
                                    </h3>
                                    <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                                        <span class="tabular-nums">{{ __(':completed / :total lessons', ['completed' => $completed, 'total' => $total]) }}</span>
                                        <span class="font-semibold text-primary tabular-nums">{{ $pct }}%</span>
                                    </div>
                                    <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ min(100, $pct) }}%"></div>
                                    </div>
                                    <a href="{{ route('courses.show', $course) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
                                        {{ $actionLabel }}
                                        <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            @if($bundleEnrollments->isNotEmpty())
                <div>
                    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
                        <div>
                            <h2 class="font-display font-bold text-xl text-navy">{{ __('Bundle progress') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Share of courses completed in each learning path') }}</p>
                        </div>
                        <a href="{{ route('bundles.my-bundles') }}" class="text-sm font-semibold text-primary hover:text-accent-dark">{{ __('View all bundles →') }}</a>
                    </div>
                    <ul class="space-y-3">
                        @foreach($bundleEnrollments as $be)
                            @php $be->refreshProgress(); @endphp
                            <li class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-wrap items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <a href="{{ route('bundles.show', $be->bundle) }}" class="font-display font-semibold text-navy hover:text-primary transition">{{ $be->bundle->tr('title') }}</a>
                                    <p class="mt-1 text-sm text-slate-500 tabular-nums">{{ __(':completed / :total courses', ['completed' => $be->completed_courses, 'total' => $be->total_courses]) }}</p>
                                </div>
                                <div class="flex items-center gap-3 min-w-[10rem]">
                                    <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ (int) $be->bundle_completion_percentage }}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="h-full rounded-full bg-accent" style="width: {{ (int) $be->bundle_completion_percentage }}%"></div>
                                    </div>
                                    <span class="text-sm font-semibold text-primary tabular-nums w-10 text-right">{{ (int) $be->bundle_completion_percentage }}%</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($otherGroupCourses->isNotEmpty())
                <div>
                    <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
                        <div>
                            <h2 class="font-display font-bold text-xl text-navy">{{ __('Courses for other groups') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ __('Made for other groups. You can read about them but not enroll') }}</p>
                        </div>
                        <a href="{{ route('courses.index') }}" class="text-sm font-semibold text-primary hover:text-accent-dark">{{ __('Browse all courses →') }}</a>
                    </div>
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($otherGroupCourses as $course)
                            @include('courses.partials.dashboard-card', ['course' => $course, 'enrolled' => false, 'locked' => true])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
