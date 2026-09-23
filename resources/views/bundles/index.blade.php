@extends('layouts.site')

@section('title', 'Bundles')

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">Bundles</li>
                </ol>
            </nav>
            <h1 class="font-display font-bold text-3xl sm:text-4xl tracking-tight">Course Bundles</h1>
            <p class="mt-3 max-w-2xl text-slate-300 leading-relaxed">
                Curated learning paths that group related courses together. Enroll once and unlock every course in the bundle.
            </p>
            @if($bundles->total() > 0)
                <p class="mt-5 text-sm text-slate-400">
                    {{ number_format($bundles->total()) }} {{ \Illuminate\Support\Str::plural('bundle', $bundles->total()) }} available
                </p>
            @endif
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($bundles->isEmpty())
                <div class="text-center py-16 px-6 rounded-2xl bg-white border border-slate-200">
                    <div class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-xl bg-primary-light text-primary mb-4">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <h2 class="font-display font-semibold text-lg text-navy">No bundles yet</h2>
                    <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">Bundles will appear here once they are published. You can still browse individual courses.</p>
                    <a href="{{ route('courses.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">Browse courses</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-7">
                    @foreach($bundles as $bundle)
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
                                <span class="inline-flex self-start px-2 py-0.5 rounded-md bg-primary-light text-primary text-[11px] font-semibold uppercase tracking-wide">
                                    {{ $bundle->courses_count }} {{ \Illuminate\Support\Str::plural('course', $bundle->courses_count) }}
                                </span>
                                <h2 class="mt-3 font-display font-semibold text-lg text-navy leading-snug">
                                    <a href="{{ route('bundles.show', $bundle) }}" class="hover:text-primary transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                                        {{ $bundle->title }}
                                    </a>
                                </h2>
                                @if(filled($bundle->description))
                                    <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit(trim(preg_replace('/\s+/', ' ', $bundle->description)), 110) }}</p>
                                @endif
                                <a href="{{ route('bundles.show', $bundle) }}" class="mt-auto pt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
                                    View bundle
                                    <span aria-hidden="true">→</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($bundles->hasPages())
                    <div class="mt-12">
                        {{ $bundles->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
