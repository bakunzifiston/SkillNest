@extends('layouts.site')

@section('title', 'My Bundles')

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ route('bundles.index') }}" class="hover:text-white transition">Bundles</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">My bundles</li>
                </ol>
            </nav>
            <h1 class="font-display font-bold text-3xl sm:text-4xl tracking-tight">My Bundles</h1>
            <p class="mt-3 max-w-2xl text-slate-300 leading-relaxed">Track progress across the learning paths you are enrolled in.</p>
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($enrollments->isEmpty())
                <div class="text-center py-16 px-6 rounded-2xl bg-white border border-slate-200">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-light text-primary mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h2 class="font-display font-semibold text-lg text-navy">No bundles yet</h2>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">You haven’t enrolled in any bundles. Browse curated learning paths to get started.</p>
                    <a href="{{ route('bundles.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">Browse bundles</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    @foreach($enrollments as $enrollment)
                        @php
                            $bundle = $enrollment->bundle;
                            $pct = (int) $enrollment->bundle_completion_percentage;
                        @endphp
                        <article class="group flex flex-col bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-primary-muted hover:shadow-brand transition">
                            <a href="{{ route('bundles.show', $bundle) }}" class="block aspect-[16/10] bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary">
                                @if($bundle->thumbnail_url)
                                    <img
                                        src="{{ $bundle->thumbnail_url }}"
                                        alt=""
                                        class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-light to-slate-100 text-primary">
                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    </div>
                                @endif
                            </a>
                            <div class="flex flex-1 flex-col p-5">
                                <h2 class="font-display font-semibold text-lg text-navy leading-snug">
                                    <a href="{{ route('bundles.show', $bundle) }}" class="hover:text-primary transition">{{ $bundle->title }}</a>
                                </h2>
                                <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                                    <span class="tabular-nums">{{ $enrollment->completed_courses }} / {{ $enrollment->total_courses }} courses</span>
                                    <span class="font-semibold text-primary tabular-nums">{{ $pct }}%</span>
                                </div>
                                <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100" aria-label="Bundle progress">
                                    <div class="h-full rounded-full bg-accent transition-all" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                                <a href="{{ route('bundles.show', $bundle) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
                                    Continue
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($enrollments->hasPages())
                    <div class="mt-12">
                        {{ $enrollments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
