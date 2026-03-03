@extends('layouts.site')

@section('title', 'My Bundles')

@section('content')
    <section class="bg-white border-b border-slate-200 py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">My bundles</h1>
            <p class="mt-2 text-slate-600">Your enrolled bundles and progress.</p>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($enrollments->isEmpty())
                <div class="text-center py-16 rounded-2xl bg-slate-50 border border-slate-100">
                    <p class="text-slate-600">You haven't enrolled in any bundles yet.</p>
                    <a href="{{ route('bundles.index') }}" class="mt-4 inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Browse bundles</a>
                </div>
            @else
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach($enrollments as $enrollment)
                        @php
                            $bundle = $enrollment->bundle;
                            $pct = (int) $enrollment->bundle_completion_percentage;
                        @endphp
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
                                <div class="mt-3 flex items-center justify-between text-sm">
                                    <span class="text-slate-600">{{ $enrollment->completed_courses }} / {{ $enrollment->total_courses }} courses</span>
                                    <span class="font-medium text-amber-600">{{ $pct }}%</span>
                                </div>
                                <div class="mt-2 h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-amber-500 transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-12">
                    {{ $enrollments->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
