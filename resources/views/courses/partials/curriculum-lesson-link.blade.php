@php
    $isCurrent = $currentLesson && $lesson->id === $currentLesson->id;
    $isCompleted = $completedLessonIds->contains($lesson->id);
@endphp

<a href="{{ route('courses.lessons.show', [$course, $lesson]) }}"
   class="flex items-center gap-2 rounded-lg py-2 pl-2 pr-2 text-sm transition {{ $isCurrent ? 'bg-white/25 text-white ring-1 ring-white/40' : 'text-white/90 hover:bg-primary-dark/70' }}"
   @if($isCurrent) aria-current="page" @endif>
    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-medium tabular-nums {{ $isCurrent ? 'bg-white text-primary' : 'bg-white/20 text-white' }}">
        {{ $lessonNumber }}
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
    <span data-lesson-id="{{ $lesson->id }}" data-role="checkmark" class="shrink-0 text-white {{ $isCompleted ? '' : 'hidden' }}" aria-label="Completed">✓</span>
</a>
