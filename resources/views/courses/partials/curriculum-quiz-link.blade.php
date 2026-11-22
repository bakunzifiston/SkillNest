@php $quizBlocker = $course->quizBlocker($quiz, $completedLessonIds ?? collect(), auth()->user()); @endphp
@if($quizBlocker)
    <div data-quiz-locked class="relative flex items-center gap-2 rounded-lg py-2 pl-2 pr-2 text-sm cursor-not-allowed" style="color:rgba(255,255,255,.85);" title="{{ __('Locked until the lessons before it are completed') }}">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md" style="background:rgba(255,255,255,.14);color:#fff;">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 11V8a5 5 0 0110 0v3M6 11h12v9H6z"/></svg>
        </span>
        <span class="min-w-0 flex-1 truncate">{{ $quiz->tr('title') }}</span>
        <span class="shrink-0 text-xs" style="color:rgba(255,255,255,.7);">{{ __('Locked') }}</span>
    </div>
@else
<a href="{{ route('courses.quizzes.show', [$course, $quiz]) }}"
   class="flex items-center gap-2 rounded-lg py-2 pl-2 pr-2 text-sm text-white/90 hover:bg-white/10 transition">
    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-accent text-[10px] font-semibold uppercase text-white">Q</span>
    <span class="min-w-0 flex-1 truncate">{{ $quiz->tr('title') }}</span>
    <span class="shrink-0 text-xs text-white/60">{{ __('Quiz') }}</span>
</a>
@endif
