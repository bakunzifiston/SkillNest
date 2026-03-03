@extends('layouts.site')

@section('title', $bundle->title)

@section('content')
    <section class="bg-white border-b border-slate-200 py-8 lg:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 text-amber-800 border border-amber-200">{{ session('info') }}</div>
            @endif

            @if($bundle->thumbnail_url)
                <div class="rounded-2xl overflow-hidden border border-slate-200 mb-6">
                    <img src="{{ $bundle->thumbnail_url }}" alt="" class="w-full aspect-video object-cover">
                </div>
            @endif
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">{{ $bundle->title }}</h1>
            <p class="mt-4 text-slate-600">{{ $bundle->courses->count() }} courses in this bundle</p>

            <div class="mt-8 flex flex-wrap gap-4">
                @if($enrolled)
                    <div class="flex items-center gap-4 flex-wrap">
                        @if($bundleEnrollment)
                            <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700">
                                <span class="font-medium">Bundle progress:</span>
                                <span>{{ $bundleEnrollment->completed_courses }} / {{ $bundleEnrollment->total_courses }} courses ({{ (int) $bundleEnrollment->bundle_completion_percentage }}%)</span>
                            </div>
                            @if($bundleEnrollment->completed_at)
                                <span class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 font-medium">Bundle completed</span>
                            @endif
                        @endif
                        <a href="{{ route('courses.my-courses') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">My courses</a>
                    </div>
                @else
                    <form action="{{ route('bundles.enroll', $bundle) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                            Enroll in this bundle
                        </button>
                    </form>
                    <p class="text-sm text-slate-500 self-center">Sign in or create an account to enroll. You’ll get access to all {{ $bundle->courses->count() }} courses.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-slate max-w-none mb-10">
                <h2 class="font-display font-bold text-xl text-slate-900 mb-4">What you’ll get</h2>
                <div class="text-slate-600 whitespace-pre-wrap">{{ $bundle->description ?? 'No description.' }}</div>
            </div>

            <h2 class="font-display font-bold text-xl text-slate-900 mb-6">Courses in this bundle</h2>
            <div class="space-y-4">
                @foreach($bundle->courses as $index => $course)
                <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-slate-200">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-semibold flex items-center justify-center text-sm">{{ $index + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <a href="{{ route('courses.show', $course) }}" class="font-medium text-slate-900 hover:text-amber-600">{{ $course->title }}</a>
                        @if($course->category)
                            <span class="text-sm text-slate-500 ml-2">— {{ $course->category->name }}</span>
                        @endif
                    </div>
                    @if($enrolled)
                        <a href="{{ route('courses.show', $course) }}" class="flex-shrink-0 text-sm font-medium text-amber-600 hover:underline">Open course</a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
