@extends('layouts.site')

@section('title', 'Bundles')

@section('content')
    <section class="bg-white border-b border-slate-200 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">Course bundles</h1>
            <p class="mt-2 text-slate-600">Save time with curated learning paths. Enroll once and get access to all courses in the bundle.</p>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($bundles->isEmpty())
                <div class="text-center py-16 rounded-2xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-500">No bundles available yet. Check back soon.</p>
                    <a href="{{ route('courses.index') }}" class="mt-4 inline-block text-amber-600 font-semibold hover:underline">Browse courses</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($bundles as $bundle)
                    <a href="{{ route('bundles.show', $bundle) }}" class="group block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-xl hover:border-amber-100 transition">
                        <div class="aspect-video bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-4xl text-slate-400">
                            @if($bundle->thumbnail_url)
                                <img src="{{ $bundle->thumbnail_url }}" alt="" class="w-full h-full object-cover">
                            @else
                                📦
                            @endif
                        </div>
                        <div class="p-5">
                            <h2 class="font-display font-semibold text-slate-900 group-hover:text-amber-700 line-clamp-2">{{ $bundle->title }}</h2>
                            <p class="mt-2 text-sm text-slate-500 line-clamp-2">{{ Str::limit($bundle->description, 80) }}</p>
                            <p class="mt-3 text-sm font-medium text-amber-600">{{ $bundle->courses_count }} courses</p>
                        </div>
                    </a>
                    @endforeach
                </div>
                <div class="mt-12">
                    {{ $bundles->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
