@extends('layouts.site')

@section('title', 'All Courses')

@section('content')
    <section class="bg-white border-b border-slate-200 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">All Courses</h1>
            <p class="mt-2 text-slate-600">Browse our full catalog and find the right course for you.</p>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-10">
                <a href="{{ route('courses.index') }}" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('category') ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">All</a>
                @foreach($categories as $cat)
                <a href="{{ route('courses.index', ['category' => $cat->slug]) }}" class="px-4 py-2 rounded-full text-sm font-medium {{ request('category') === $cat->slug ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ $cat->name }}</a>
                @endforeach
            </div>
            @endif

            @if($courses->isEmpty())
                <div class="text-center py-16 rounded-2xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-500">No courses found. Check back soon or try another category.</p>
                    <a href="{{ route('home') }}" class="mt-4 inline-block text-amber-600 font-semibold hover:underline">Back to Home</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($courses as $course)
                    <article class="group block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-amber-100 transition">
                        <a href="{{ route('courses.show', $course) }}" class="block">
                            <div class="aspect-video bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-4xl text-slate-400">
                                @if($course->banner_url)
                                    <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    📖
                                @endif
                            </div>
                            <div class="p-5">
                                @if($course->category ?? null)
                                <span class="text-xs font-medium text-amber-600 uppercase tracking-wide">{{ $course->category->name }}</span>
                                @endif
                                <h2 class="mt-2 font-display font-semibold text-slate-900 group-hover:text-amber-700 line-clamp-2">{{ $course->title }}</h2>
                                <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit($course->description ?? '', 80) }}</p>
                                <div class="mt-4 flex items-center justify-between text-sm">
                                    <span class="text-slate-500">{{ $course->duration ?? 'Self-paced' }}</span>
                                    <span class="font-semibold text-amber-600">@if(($course->price ?? 0) > 0) ${{ number_format($course->price, 0) }} @else Free @endif</span>
                                </div>
                            </div>
                        </a>
                    </article>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $courses->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
