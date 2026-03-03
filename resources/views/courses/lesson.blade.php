@extends('layouts.site')

@section('title', $lesson->title . ' — ' . $course->title)

@section('content')
    <div class="flex flex-col lg:flex-row bg-slate-100 min-h-screen">
        {{-- Sidebar: chapters & lessons --}}
        <aside class="lg:w-72 lg:min-w-[18rem] flex-shrink-0 bg-white border-b lg:border-b-0 lg:border-r border-slate-200">
            <div class="p-4 border-b border-slate-200 bg-slate-50">
                <a href="{{ route('courses.show', $course) }}" class="text-sm text-amber-600 hover:text-amber-700 font-medium">← Back to course</a>
                <h2 class="mt-2 font-display font-semibold text-slate-900 line-clamp-2">{{ $course->title }}</h2>
            </div>
            <nav class="py-3 overflow-y-auto max-h-[50vh] lg:max-h-[calc(100vh-12rem)]" aria-label="Course curriculum">
                @foreach($course->chapters as $chapter)
                    <div class="mb-2">
                        <div class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $chapter->title }}</div>
                        <ul class="space-y-0.5">
                            @foreach($chapter->lessons as $l)
                                <li>
                                    <a href="{{ route('courses.lessons.show', [$course, $l]) }}" class="flex items-center gap-2 py-2 px-4 text-sm border-l-2 {{ $l->id === $lesson->id ? 'border-amber-500 bg-amber-50 text-amber-800 font-medium' : 'border-transparent text-slate-700 hover:bg-slate-50' }}">
                                        <span class="flex-1 min-w-0 truncate">{{ $l->title }}</span>
                                        @if($completedLessonIds->contains($l->id))
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">✓</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 min-w-0 py-6 lg:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
                @endif
                <nav class="flex items-center gap-2 text-sm text-slate-600 mb-6">
                    <a href="{{ route('courses.show', $course) }}" class="hover:text-amber-600">{{ $course->title }}</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-slate-900 font-medium">{{ $lesson->title }}</span>
                </nav>
                <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-6 lg:p-8">
                        <h1 class="font-display font-bold text-2xl lg:text-3xl text-slate-900">{{ $lesson->title }}</h1>
                        <div class="mt-6 prose prose-slate max-w-none">
                            @switch($lesson->type)
                                @case(\App\Models\Lesson::TYPE_TEXT)
                                    <div class="whitespace-pre-wrap text-slate-700">{{ $lesson->content ?? 'No content.' }}</div>
                                    @break
                                @case(\App\Models\Lesson::TYPE_YOUTUBE)
                                    @if(!empty($lesson->source_url))
                                        @php
                                            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $lesson->source_url, $m);
                                            $vid = $m[1] ?? null;
                                        @endphp
                                        @if($vid)
                                            <div class="aspect-video rounded-xl overflow-hidden bg-slate-900">
                                                <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $vid }}" title="YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                            </div>
                                        @else
                                            <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="text-amber-600 hover:underline">Watch on YouTube</a>
                                        @endif
                                    @else
                                        <p class="text-slate-500">No video link provided.</p>
                                    @endif
                                    @if(!empty($lesson->content))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_PDF)
                                    @if(!empty($lesson->file_path))
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($lesson->file_path) }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600">
                                            Download / View PDF
                                        </a>
                                    @elseif(!empty($lesson->source_url))
                                        <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600">
                                            Open PDF
                                        </a>
                                    @endif
                                    @if(!empty($lesson->content))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_VIDEO)
                                    @if(!empty($lesson->file_path))
                                        <video class="w-full rounded-xl border border-slate-200" controls>
                                            <source src="{{ \Illuminate\Support\Facades\Storage::url($lesson->file_path) }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @elseif(!empty($lesson->source_url))
                                        <video class="w-full rounded-xl border border-slate-200" controls>
                                            <source src="{{ $lesson->source_url }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <p class="text-slate-500">No video file available.</p>
                                    @endif
                                    @if(!empty($lesson->content))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @default
                                    <div class="whitespace-pre-wrap text-slate-700">{{ $lesson->content ?? 'No content.' }}</div>
                            @endswitch
                        </div>

                        <div class="mt-10 pt-8 border-t border-slate-200 flex flex-wrap gap-4">
                            @if(!$completed)
                                <form action="{{ route('lessons.complete', $lesson) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                                        Mark as complete
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center gap-2 text-emerald-600 font-medium">✓ Completed</span>
                            @endif
                            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">Back to course</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
@endsection
