@php
    $currentLesson = $currentLesson ?? null;
    $completedLessonIds = $completedLessonIds ?? collect();
    $sidebarClass = $sidebarClass ?? '';
    $lessonNumber = 0;
@endphp

<aside class="lg:w-72 lg:min-w-[18rem] flex-shrink-0 bg-primary border-b lg:border-b-0 lg:border-r border-primary-dark {{ $sidebarClass }}">
    <div class="px-3 py-3 border-b border-white/20">
        <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center gap-1.5 text-sm text-white/90 hover:text-white transition">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Course overview</span>
        </a>
        @if($completedCount !== null && $totalLessons !== null && $totalLessons > 0)
            <div class="mt-3">
                <div class="flex justify-between text-xs text-white/80 mb-1">
                    <span>Progress</span>
                    <span data-course-progress-label>{{ $completedCount }} / {{ $totalLessons }}</span>
                </div>
                <div class="h-1.5 rounded-full bg-white/25 overflow-hidden">
                    <div data-course-progress-bar class="h-full rounded-full bg-accent transition-all" style="width: {{ min(100, round(($completedCount / $totalLessons) * 100)) }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <nav class="py-2 overflow-y-auto max-h-[50vh] lg:max-h-[calc(100vh-10rem)]" aria-label="Course curriculum">
        @forelse($course->chapters as $chapter)
            @php
                $chapterLessons = $chapter->lessons;
                $isActiveChapter = $currentLesson && $chapterLessons->contains('id', $currentLesson->id);
                $singleLessonMatchesChapter = $chapterLessons->count() === 1
                    && strcasecmp(trim($chapter->title), trim($chapterLessons->first()->title)) === 0;
            @endphp

            @if($singleLessonMatchesChapter)
                @php $l = $chapterLessons->first(); $lessonNumber++; @endphp
                <ul class="px-2 py-0.5">
                    <li>
                        @include('courses.partials.curriculum-lesson-link', [
                            'course' => $course,
                            'lesson' => $l,
                            'currentLesson' => $currentLesson,
                            'completedLessonIds' => $completedLessonIds,
                            'lessonNumber' => $lessonNumber,
                            'label' => $chapter->title,
                        ])
                    </li>
                </ul>
            @else
                <details class="group border-b border-white/15 last:border-b-0" @if($isActiveChapter) open @endif>
                    <summary class="flex cursor-pointer list-none items-center gap-2 px-3 py-2.5 text-sm font-medium text-white hover:bg-primary-dark/80 [&::-webkit-details-marker]:hidden">
                        <svg class="w-4 h-4 shrink-0 text-white/70 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span class="min-w-0 flex-1 line-clamp-2">{{ $chapter->title }}</span>
                        <span class="shrink-0 text-xs text-white/70">{{ $chapterLessons->count() }}</span>
                    </summary>
                    <ul class="space-y-0.5 pb-2 pl-2 pr-2">
                        @foreach($chapterLessons as $l)
                            @php
                                $lessonNumber++;
                                $label = strcasecmp(trim($l->title), trim($chapter->title)) === 0
                                    ? 'Overview'
                                    : $l->title;
                            @endphp
                            <li>
                                @include('courses.partials.curriculum-lesson-link', [
                                    'course' => $course,
                                    'lesson' => $l,
                                    'currentLesson' => $currentLesson,
                                    'completedLessonIds' => $completedLessonIds,
                                    'lessonNumber' => $lessonNumber,
                                    'label' => $label,
                                ])
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
        @empty
            <p class="px-3 py-4 text-sm text-white/80">No lessons yet.</p>
        @endforelse
    </nav>
</aside>
