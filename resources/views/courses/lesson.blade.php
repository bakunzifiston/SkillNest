@extends('layouts.site')

@section('title', $lesson->tr('title') . ' — ' . $course->tr('title'))

@section('content')
    @php
        $youtubeId = $lesson->youtubeVideoId();

        $allLessons = $course->chapters->flatMap(fn ($ch) => $ch->lessons)->values();
        $currentIndex = $allLessons->search(fn ($l) => (int) $l->id === (int) $lesson->id);
        $previousLesson = $currentIndex !== false && $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex !== false && $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;
        $lessonPosition = $currentIndex === false ? null : $currentIndex + 1;
        $progressPct = $totalLessons > 0 ? min(100, (int) round(($completedCount / $totalLessons) * 100)) : 0;
        $chapterTitle = $lesson->chapter?->tr('title');
    @endphp

    <div class="flex flex-col lg:flex-row min-h-[calc(100vh-5rem)] bg-slate-50">
        @include('courses.partials.curriculum-sidebar', [
            'course' => $course,
            'currentLesson' => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'lessonStatuses' => $lessonStatuses ?? [],
            'completedCount' => $completedCount,
            'totalLessons' => $totalLessons,
        ])

        <div class="flex-1 min-w-0">
            <div class="border-b border-slate-200 bg-white">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-5">
                    <nav class="text-sm text-slate-500 mb-3" aria-label="{{ __('Breadcrumb') }}">
                        <ol class="flex flex-wrap items-center gap-2">
                            <li><a href="{{ route('courses.index') }}" class="hover:text-primary transition">{{ __('Courses') }}</a></li>
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition truncate max-w-[10rem] sm:max-w-[16rem]">{{ $course->tr('title') }}</a></li>
                            <li aria-hidden="true">/</li>
                            <li class="text-navy font-medium truncate max-w-[10rem] sm:max-w-[16rem]">{{ $lesson->tr('title') }}</li>
                        </ol>
                    </nav>
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div class="min-w-0">
                            @if($chapterTitle && strcasecmp(trim($chapterTitle), trim($lesson->tr('title'))) !== 0)
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $chapterTitle }}</p>
                            @endif
                            <h1 class="mt-1 font-display font-bold text-2xl sm:text-3xl text-navy tracking-tight">{{ $lesson->tr('title') }}</h1>
                            @php $headerStatus = $completed ? 'completed' : (($lessonStatus ?? 'open') === 'in_progress' ? 'in_progress' : 'open'); @endphp
                            <div id="lesson-status-badge" class="mt-2">@include('courses.partials.lesson-status', ['status' => $headerStatus])</div>
                            @if($lessonPosition && $totalLessons > 0)
                                <p class="mt-2 text-sm text-slate-500 tabular-nums">
                                    {{ __('Lesson :position of :total', ['position' => $lessonPosition, 'total' => $totalLessons]) }}
                                    <span class="text-slate-300 mx-1.5" aria-hidden="true">·</span>
                                    {{ __(':percent% course progress', ['percent' => $progressPct]) }}
                                </p>
                            @endif
                        </div>
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:text-accent-dark transition shrink-0">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            {{ __('Course overview') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200" role="alert">{{ session('error') }}</div>
                @endif
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
                    data-msg-completed="{{ __('Completed') }}"
                    data-msg-save-error="{{ __('Could not save your progress. Please reload the page.') }}"
                    data-msg-video-title="{{ __('Lesson video') }}"
                >
                    <div class="p-5 sm:p-7 lg:p-8">
                        <div class="prose prose-slate max-w-none" data-lesson-content>
                            @switch($lesson->type)
                                @case(\App\Models\Lesson::TYPE_TEXT)
                                    @if(filled(trim((string) $lesson->tr('content'))))
                                        <div class="whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->tr('content') }}</div>
                                    @else
                                        <p class="text-slate-500">{{ __('Lesson content will be added soon.') }}</p>
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
                                                    aria-label="{{ __('Play video') }}"
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
                                        <p class="text-slate-500 not-prose">{{ __('No playable YouTube video was found for this lesson.') }}</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->tr('content'))))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->tr('content') }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_PDF)
                                    @if(!empty($lesson->file_path))
                                        <a href="{{ $lesson->fileUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                                            {{ __('Download / View PDF') }}
                                        </a>
                                    @elseif(!empty($lesson->source_url))
                                        <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                                            {{ __('Open PDF') }}
                                        </a>
                                    @else
                                        <p class="text-slate-500">{{ __('No PDF file available.') }}</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->tr('content'))))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->tr('content') }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_VIDEO)
                                    @if(!empty($lesson->file_path))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200 bg-slate-900" controls>
                                            <source src="{{ $lesson->fileUrl() }}" type="video/mp4">
                                            {{ __('Your browser does not support the video tag.') }}
                                        </video>
                                    @elseif(!empty($lesson->source_url))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200 bg-slate-900" controls>
                                            <source src="{{ $lesson->source_url }}" type="video/mp4">
                                            {{ __('Your browser does not support the video tag.') }}
                                        </video>
                                    @else
                                        <p class="text-slate-500">{{ __('No video file available.') }}</p>
                                    @endif
                                    @if(filled(trim((string) $lesson->tr('content'))))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->tr('content') }}</div>
                                    @endif
                                    @break
                                @default
                                    @if(filled(trim((string) $lesson->tr('content'))))
                                        <div class="whitespace-pre-wrap text-slate-700 leading-relaxed">{{ $lesson->tr('content') }}</div>
                                    @else
                                        <p class="text-slate-500">{{ __('Lesson content will be added soon.') }}</p>
                                    @endif
                            @endswitch
                            <div data-lesson-scroll-marker class="h-1 w-full" aria-hidden="true"></div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-200">
                            <div id="lesson-complete-control" class="flex flex-col gap-2" data-completed="{{ $completed ? '1' : '0' }}">
                                @if($completed)
                                    <span data-lesson-complete-done class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-success-light text-success-darker text-sm font-semibold w-fit">
                                        ✓ {{ __('Completed') }}
                                    </span>
                                @else
                                    <div data-lesson-complete-incomplete>
                                        <p id="lesson-progress-status" class="text-sm text-slate-500">
                                            @switch($lesson->type)
                                                @case('youtube')
                                                @case('video')
                                                    {{ __('This lesson is marked complete automatically when you finish watching the video.') }}
                                                    @break
                                                @default
                                                    {{ __('This lesson is marked complete automatically once you have read it.') }}
                                            @endswitch
                                        </p>
                                    </div>
                                    <span data-lesson-complete-done class="hidden inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-success-light text-success-darker text-sm font-semibold w-fit">
                                        ✓ {{ __('Completed') }}
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
                            {{ __('Previous') }}
                        </a>
                    @else
                        <span></span>
                    @endif

                    @php
                        $nextLocked = $nextLesson && (($lessonStatuses ?? [])[$nextLesson->id] ?? 'open') === 'locked';
                        $quizFirst = $nextLesson && ($pendingQuiz ?? null);
                    @endphp
                    @if($nextLesson && $quizFirst)
                        @if(! $completed)
                            <span data-next-locked class="inline-flex flex-col items-end gap-1">
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-200 text-slate-500 font-semibold cursor-not-allowed" aria-disabled="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V8a5 5 0 0110 0v3M6 11h12v9H6z"/></svg>
                                    {{ __('Next lesson') }}
                                </span>
                                <span class="text-xs text-slate-500">{{ __('Complete this lesson and take its quiz to unlock the next one.') }}</span>
                            </span>
                        @endif
                        <span data-next-quiz class="{{ $completed ? '' : 'hidden ' }}inline-flex flex-col items-end gap-1">
                            <a href="{{ route('courses.quizzes.show', [$course, $pendingQuiz]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:opacity-90 transition">
                                {{ __('Take the quiz to continue') }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                            <span class="text-xs text-slate-500">{{ __('Take the quiz ":quiz" to unlock the next lesson.', ['quiz' => $pendingQuiz->tr('title')]) }}</span>
                        </span>
                    @elseif($nextLesson)
                        @if($nextLocked)
                            <span data-next-locked class="inline-flex flex-col items-end gap-1">
                                <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-200 text-slate-500 font-semibold cursor-not-allowed" aria-disabled="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11V8a5 5 0 0110 0v3M6 11h12v9H6z"/></svg>
                                    {{ __('Next lesson') }}
                                </span>
                                <span class="text-xs text-slate-500">{{ __('Complete this lesson to unlock the next one.') }}</span>
                            </span>
                        @endif
                        <a href="{{ route('courses.lessons.show', [$course, $nextLesson]) }}" @if($nextLocked) data-next-open data-next-id="{{ $nextLesson->id }}" @endif class="{{ $nextLocked ? 'hidden ' : '' }}inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">
                            {{ __('Next lesson') }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primary-dark transition">
                            {{ __('Back to course') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Unlocks the next lesson in the page the moment this one is marked completed.
        (function () {
            var control = document.getElementById('lesson-complete-control');
            if (!control) { return; }
            var done = false;
            function unlock() {
                if (done || control.dataset.completed !== '1') { return; }
                done = true;
                var locked = document.querySelector('[data-next-locked]');
                var open = document.querySelector('[data-next-open]');
                var quiz = document.querySelector('[data-next-quiz]');
                if (quiz) { if (locked) { locked.classList.add('hidden'); } quiz.classList.remove('hidden'); }
                if (locked) { locked.classList.add('hidden'); }
                if (open) {
                    open.classList.remove('hidden');
                    var id = open.getAttribute('data-next-id');
                    var row = document.querySelector('[data-lock-row="' + id + '"]');
                    var link = document.querySelector('[data-open-row="' + id + '"]');
                    if (row) { row.classList.add('hidden'); }
                    if (link) { link.classList.remove('hidden'); }
                }
                var badge = document.getElementById('lesson-status-badge');
                if (badge) { badge.classList.add('hidden'); }
                document.querySelectorAll('[data-role="progress-dot"]').forEach(function (d) { if (d.closest('[aria-current]')) { d.classList.add('hidden'); } });
            }
            new MutationObserver(unlock).observe(control, { attributes: true, attributeFilter: ['data-completed'] });
            unlock();
        })();
    </script>
    @vite('resources/js/lesson-progress.js')
@endpush
