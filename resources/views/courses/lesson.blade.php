@extends('layouts.site')

@section('title', $lesson->title . ' — ' . $course->title)

@section('content')
    @php
        $youtubeId = null;
        if ($lesson->type === \App\Models\Lesson::TYPE_YOUTUBE && ! empty($lesson->source_url)) {
            preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $lesson->source_url, $youtubeMatch);
            $youtubeId = $youtubeMatch[1] ?? null;
        }
    @endphp

    <div class="flex flex-col lg:flex-row bg-slate-100 min-h-screen">
        @include('courses.partials.curriculum-sidebar', [
            'course' => $course,
            'currentLesson' => $lesson,
            'completedLessonIds' => $completedLessonIds,
            'completedCount' => $completedCount,
            'totalLessons' => $totalLessons,
        ])

        <div class="flex-1 min-w-0 py-6 lg:py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-success-light text-success-darker border border-success-muted">{{ session('success') }}</div>
                @endif
                <nav class="flex items-center gap-2 text-sm text-slate-600 mb-6">
                    <a href="{{ route('courses.show', $course) }}" class="hover:text-primary">{{ $course->title }}</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-slate-900 font-medium">{{ $lesson->title }}</span>
                </nav>
                <article
                    class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm"
                    data-lesson-progress
                    data-lesson-id="{{ $lesson->id }}"
                    data-lesson-type="{{ $lesson->type }}"
                    data-complete-url="{{ route('lessons.complete', $lesson) }}"
                    data-already-completed="{{ $completed ? '1' : '0' }}"
                    @if($youtubeId) data-youtube-id="{{ $youtubeId }}" @endif
                >
                    <div class="p-6 lg:p-8">
                        <h1 class="font-display font-bold text-2xl lg:text-3xl text-slate-900">{{ $lesson->title }}</h1>
                        <div class="mt-6 prose prose-slate max-w-none" data-lesson-content>
                            @switch($lesson->type)
                                @case(\App\Models\Lesson::TYPE_TEXT)
                                    <div class="whitespace-pre-wrap text-slate-700">{{ $lesson->content ?? 'No content.' }}</div>
                                    @break
                                @case(\App\Models\Lesson::TYPE_YOUTUBE)
                                    @if($youtubeId)
                                        <div id="lesson-youtube-player" class="aspect-video rounded-xl overflow-hidden bg-slate-900"></div>
                                    @elseif(!empty($lesson->source_url))
                                        <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="text-primary hover:underline">Watch on YouTube</a>
                                    @else
                                        <p class="text-slate-500">No video link provided.</p>
                                    @endif
                                    @if(!empty($lesson->content))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_PDF)
                                    @if(!empty($lesson->file_path))
                                        <a href="{{ $lesson->fileUrl() }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark">
                                            Download / View PDF
                                        </a>
                                    @elseif(!empty($lesson->source_url))
                                        <a href="{{ $lesson->source_url }}" target="_blank" rel="noopener" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark">
                                            Open PDF
                                        </a>
                                    @endif
                                    @if(!empty($lesson->content))
                                        <div class="mt-6 whitespace-pre-wrap text-slate-700">{{ $lesson->content }}</div>
                                    @endif
                                    @break
                                @case(\App\Models\Lesson::TYPE_VIDEO)
                                    @if(!empty($lesson->file_path))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200" controls>
                                            <source src="{{ $lesson->fileUrl() }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    @elseif(!empty($lesson->source_url))
                                        <video data-lesson-video class="w-full rounded-xl border border-slate-200" controls>
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
                            <div data-lesson-scroll-marker class="h-1 w-full" aria-hidden="true"></div>
                        </div>

                        <div class="mt-10 pt-8 border-t border-slate-200 flex flex-wrap items-center gap-4">
                            <div id="lesson-complete-control" class="flex flex-col gap-2" data-completed="{{ $completed ? '1' : '0' }}">
                                @if($completed)
                                    <span data-lesson-complete-done class="inline-flex items-center gap-2 text-success-dark font-medium">
                                        ✓ Completed
                                    </span>
                                @else
                                    <div data-lesson-complete-incomplete class="flex flex-wrap items-center gap-4">
                                        <form data-lesson-complete-form action="{{ route('lessons.complete', $lesson) }}" method="POST" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                data-lesson-complete-btn
                                                class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition disabled:opacity-60 disabled:cursor-not-allowed"
                                            >
                                                Mark as complete
                                            </button>
                                        </form>
                                        <p id="lesson-progress-status" class="text-sm text-slate-500">
                                            Or finish automatically as you watch or read this lesson.
                                        </p>
                                    </div>
                                    <span data-lesson-complete-done class="hidden inline-flex items-center gap-2 text-success-dark font-medium">
                                        ✓ Completed
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">Back to course</a>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/lesson-progress.js')
@endpush
