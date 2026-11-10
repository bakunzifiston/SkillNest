<a href="{{ route('courses.quizzes.show', [$course, $quiz]) }}"
   class="flex items-center gap-2 rounded-lg py-2 pl-2 pr-2 text-sm text-white/90 hover:bg-white/10 transition">
    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-accent text-[10px] font-semibold uppercase text-white">Q</span>
    <span class="min-w-0 flex-1 truncate">{{ $quiz->title }}</span>
    <span class="shrink-0 text-xs text-white/60">Quiz</span>
</a>
