{{-- Dashboard course card. Expects $course (with category + chapters.lessons), $enrolled (bool), optional $locked (bool): meant for another group. --}}
@php
    $locked = $locked ?? false;
    $total = $course->chapters->sum(fn ($ch) => $ch->lessons->count());
    $completed = $enrolled ? auth()->user()->completedLessonsCountForCourse($course) : 0;
    $pct = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    $actionLabel = $locked ? __('View details') : (! $enrolled ? __('View course') : ($pct >= 100 ? __('View') : ($completed > 0 ? __('Resume') : __('Start'))));
@endphp
<article class="group flex flex-col rounded-2xl border border-slate-200 bg-white overflow-hidden hover:border-primary-muted hover:shadow-brand transition">
    <a href="{{ route('courses.show', $course) }}" class="block aspect-[16/10] bg-slate-100 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary">
        @if($course->banner_url)
            <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover transition duration-500 group-hover:scale-[1.03]" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-primary-light to-slate-100 text-primary">
                @include('partials.category-icon', ['slug' => $course->category->slug ?? 'book', 'name' => $course->category?->tr('name') ?? '', 'class' => 'h-10 w-10'])
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-5">
        <div class="flex flex-wrap items-center gap-2">
            @if($course->category)
                <span class="inline-flex px-2 py-0.5 rounded-md bg-primary-light text-primary text-[11px] font-semibold uppercase tracking-wide">{{ $course->category->tr('name') }}</span>
            @endif
            @if($locked)
                @include('courses.partials.access-badge')
            @elseif($enrolled)
                <span class="inline-flex px-2 py-0.5 rounded-md bg-success-light text-success-darker text-[11px] font-semibold">{{ __('Enrolled') }}</span>
            @else
                <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 text-[11px] font-semibold">{{ __('Not enrolled yet') }}</span>
            @endif
        </div>
        <h3 class="mt-2 font-display font-semibold text-navy leading-snug">
            <a href="{{ route('courses.show', $course) }}" class="hover:text-primary transition">{{ $course->tr('title') }}</a>
        </h3>
        @if($enrolled)
            <div class="mt-3 flex items-center justify-between text-sm text-slate-600">
                <span class="tabular-nums">{{ __(':completed / :total lessons', ['completed' => $completed, 'total' => $total]) }}</span>
                <span class="font-semibold text-primary tabular-nums">{{ $pct }}%</span>
            </div>
            <div class="mt-2 h-1.5 rounded-full bg-slate-100 overflow-hidden" role="progressbar" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-accent" style="width: {{ min(100, $pct) }}%"></div>
            </div>
        @else
            <p class="mt-3 text-sm text-slate-500 tabular-nums">{{ trans_choice('{1} :count lesson|[2,*] :count lessons', $total) }}</p>
        @endif
        <a href="{{ route('courses.show', $course) }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-primary hover:text-accent-dark">
            {{ $actionLabel }}
            <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
