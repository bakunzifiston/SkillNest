@php
    $isCurrent = $currentLesson && $lesson->id === $currentLesson->id;
    $isCompleted = $completedLessonIds->contains($lesson->id);
    $status = ($lessonStatuses ?? [])[$lesson->id] ?? ($isCompleted ? 'completed' : 'open');
    $isLocked = $status === 'locked';
    $rowClass = 'flex items-center gap-2 rounded-lg py-2 pl-2 pr-2 text-sm transition';
@endphp

@if($isLocked)
    <div data-lock-row="{{ $lesson->id }}" class="{{ $rowClass }} text-white/55 cursor-not-allowed" title="{{ __('Locked until the previous lesson is completed') }}">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-white/10 text-white/70">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M7 11V8a5 5 0 0110 0v3M6 11h12v9H6z"/></svg>
        </span>
        <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
        <span class="sr-only">{{ __('Locked') }}</span>
    </div>
@endif

<a href="{{ route('courses.lessons.show', [$course, $lesson]) }}"
   @if($isLocked) data-open-row="{{ $lesson->id }}" @endif
   class="{{ $rowClass }} {{ $isLocked ? 'hidden' : '' }} {{ $isCurrent ? 'bg-white/25 text-white ring-1 ring-white/40' : 'text-white/90 hover:bg-white/10' }}"
   @if($isCurrent) aria-current="page" @endif>
    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md text-xs font-medium tabular-nums {{ $isCurrent ? 'bg-white text-primary' : 'bg-white/20 text-white' }}">
        {{ $lessonNumber }}
    </span>
    <span class="min-w-0 flex-1 truncate">{{ $label }}</span>
    @if($status === 'in_progress')
        <span data-role="progress-dot" class="shrink-0" title="{{ __('In progress') }}"><span class="sr-only">{{ __('In progress') }}</span><span style="display:block;width:8px;height:8px;border-radius:50%;background:#F16029;"></span></span>
    @endif
    <span data-lesson-id="{{ $lesson->id }}" data-role="checkmark" class="shrink-0 text-white {{ $isCompleted ? '' : 'hidden' }}" aria-label="{{ __('Completed') }}">✓</span>
</a>
