@extends('layouts.site')

@section('title', $lesson->title . ' — ' . $course->title)

@section('content')
    @php
        $youtubeId = $lesson->youtubeVideoId();

        $allLessons = $course->chapters->flatMap(fn ($ch) => $ch->lessons)->values();
        $currentIndex = $allLessons->search(fn ($l) => (int) $l->id === (int) $lesson->id);
        $previousLesson = $currentIndex !== false && $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;
        $lessonPosition = $currentIndex === false ? null : $currentIndex + 1;
        $progressPct = $totalLessons > 0 ? min(100, (int) round(($completedCount / $totalLessons) * 100)) : 0;
        $chapterTitle = $lesson->chapter->title ?? null;
    @endphp

    <div class="flex flex-col lg:flex-row min-h-[calc(100vh-5rem)] bg-slate-50">
        @include('courses.partials.curriculum-sidebar', [
            'course' => $course,
            'currentLesson' => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'completedCount' => $completedCount,
            'totalLessons' => $totalLessons,
        ])

        <div class="flex-1 min-w-0">
            <div class="border-b border-slate-200 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
                    <nav class="text-sm text-slate-500 mb-3" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-2">
                            <li><a href="{{ route('courses.index') }}" class="hover:text-primary transition">Courses</a></li>
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition truncate max-w-[10rem] sm:max-w-[16rem]">{{ $course->title }}</a></li>
                            <li aria-hidden="true">/</li>
                            <li class="text-navy font-medium truncate max-w-[10rem] sm:max-w-[16rem]">{{ $lesson->title }}</li>
                        </ol>
                    </nav>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="min-w-0">
                            @if($chapterTitle && strcasecmp(trim($chapterTitle), trim($lesson->title)) !== 0)
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $chapterTitle }}</p>
                            @endif
                            <h1 class="mt-1 font-display font-bold text-2xl sm:text-3xl text-navy tracking-tight">{{ $lesson->title }}</h1>
                            @if($lessonPosition && $totalLessons > 0)
                                <p class="mt-2 text-sm text-slate-500 tabular-nums">
                                    Lesson {{ $lessonPosition }} of {{ $totalLessons }}
                                    <span class="text-slate-300 mx-1.5" aria-hidden="true">·</span>
                                    {{ $progressPct }}% course progress
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-accent-dark transition shrink-0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Course overview
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-success-light text-success-darker border border-success-muted">{{ session('success') }}</div>
                @endif
                @if(session('info'))
                    <div class="mb-6 p-4 rounded-xl bg-accent-light text-accent-darker border border-accent-muted">{{ session('info') }}</div>
                @endif

                <article
                    class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm"
                    data-lesson-progress
                    data-lesson-id="{{ $lesson->id }}"
                    data-lesson-type="{{ $lesson->type }}"
                    data-complete-url="{{ route('lessons.complete', $lesson) }}"
                    data-already-completed="{{ $completed ? '1' : '0' }}"
                    @if($youtubeId) data-youtube-id="{{ $youtubeId }}" @endif
                >
                    <div class="p-5 sm:p-7 lg:p-8">
                        <div class="prose prose-slate max-w-none" data-lesson-content>
                            @switch($lesson->type)
                                @case(\App\Models\Lesson::TYPE_TEXT)
                                    @if(filled(trim((string) $lesson->content)))
                                        <div class="whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->content }}</div>
                                    @else
                                        <p class="text-slate-500">Lesson content will be added soon.</p>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_YOUTUBE)
                                    @if($youtubeId)
                                        <div class="not-prose">
                                            <div
                                                class="relative aspect-video w-full rounded-xl overflow-hidden bg-slate-900 ring-1 ring-slate-200"
                                                data-youtube-player
                                                data-youtube-id="{{ $youtubeId }}"
                                            >
                                                <button
                                                    type="button"
                                                    data-youtube-play
                                                    class="group absolute inset-0 z-10 flex items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-inset"
                                                    aria-label="Play video"
                                                >
                                                    <img
                                                        src="https://i.ytimg.com/vi/{{ $youtubeId }}/hqdefault.jpg"
                                                        alt=""
                                                        class="absolute inset-0 h-full w-full object-cover"
                                                        loading="lazy"
                                                    >
                                                    <span class="absolute inset-0 bg-slate-900/35 group-hover:bg-slate-900/45 transition"></span>
                                                    <span class="relative inline-flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-accent text-white shadow-brand-accent group-hover:scale-105 transition">
                                                        <svg class="h-7 w-7 sm:h-8 sm:w-8 ml-1" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7L8 5z"/></svg>
                                                    </span>
                                                </button>
                                                <div data-youtube-frame class="absolute inset-0 hidden"></div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-slate-500 not-prose">No playable YouTube video was found for this lesson.</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->content)))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_PDF)
                                    @if(!empty($lesson->file_path))
                                        <a href="{{ $lesson->fileUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                                            Download / View PDF
                                        </a>
                                    @elseif(!empty($lesson->source_url))
                                        <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                                            Open PDF
                                        </a>
                                    @else
                                        <p class="text-slate-500">No PDF file available.</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->content)))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_VIDEO)
                                    @if(!empty($lesson->file_path))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200 bg-slate-900" controls>
                                            <source src="{{ $lesson->fileUrl() }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @elseif(!empty($lesson->source_url))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200 bg-slate-900" controls>
                                            <source src="{{ $lesson->source_url }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @else
                                        <p class="text-slate-500">No video file available.</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->content)))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @default
                                    @if(filled(trim((string) $lesson->content)))
                                        <div class="whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->content }}</div>
                                    @else
                                        <p class="text-slate-500">Lesson content will be added soon.</p>
                                    @endif
                            @endswitch
                            <div data-lesson-scroll-marker class="h-1 w-full" aria-hidden="true"></div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-200">
                            <div id="lesson-complete-control" class="flex flex-col gap-2" data-completed="{{ $completed ? '1' : '0' }}">
                                @if($completed)
                                    <span data-lesson-complete-done class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-success-light text-success-darker text-sm font-semibold w-fit">
                                        ✓ Completed
                                    </span>
                                @else
                                    <div data-lesson-complete-incomplete class="flex flex-wrap items-center gap-4">
                                        <form data-lesson-complete-form action="{{ route('lessons.complete', $lesson) }}" method="POST" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                data-lesson-complete-btn
                                                class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                                            >
                                                Mark as complete
                                            </button>
                                        </form>
                                        <p id="lesson-progress-status" class="text-sm text-slate-500">
                                            Or finish automatically as you watch or read this lesson.
                                        </p>
                                    </div>
                                    <span data-lesson-complete-done class="hidden inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-success-light text-success-darker text-sm font-semibold w-fit">
                                        ✓ Completed
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                    @if($previousLesson)
                        <a href="{{ route('courses.lessons.show', [$course, $previousLesson]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-navy font-medium hover:border-primary-muted hover:text-primary transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Previous
                        </a>
                    @else
                        <span></span>
                    @endif

                    @if($nextLesson)
                        <a href="{{ route('courses.lessons.show', [$course, $nextLesson]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">
                            Next lesson
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">
                            Back to course
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/lesson-progress.js')
@endpush
