<li class="bg-primary-light/40">
    @if($enrolled)
        <a href="{{ route('courses.quizzes.show', [$course, $quiz]) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-primary-light transition group">
            <span class="flex-shrink-0 w-6 text-[10px] font-semibold uppercase tracking-wide text-primary">Quiz</span>
            <span class="flex-1 min-w-0">
                <span class="block font-medium text-slate-700 group-hover:text-primary transition">{{ $quiz->title }}</span>
                <span class="block text-xs text-slate-500">Passing grade: {{ $quiz->passing_grade }}% · {{ $quiz->questions_count ?? 0 }} questions</span>
            </span>
            <svg class="h-4 w-4 text-slate-300 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    @else
        <div class="flex items-center gap-3 px-5 py-3.5">
            <span class="flex-shrink-0 w-6 text-[10px] font-semibold uppercase tracking-wide text-primary">Quiz</span>
            <span class="flex-1 min-w-0">
                <span class="block font-medium text-slate-700">{{ $quiz->title }}</span>
                <span class="block text-xs text-slate-500">Passing grade: {{ $quiz->passing_grade }}% · {{ $quiz->questions_count ?? 0 }} questions</span>
            </span>
            <span class="text-xs text-slate-400">Enroll to access</span>
        </div>
    @endif
</li>
