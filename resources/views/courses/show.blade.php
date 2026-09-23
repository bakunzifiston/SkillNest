@extends('layouts.site')

@section('title', $course->title)

@section('content')
    <div class="flex flex-col lg:flex-row">
        @if($enrolled && $course->chapters->isNotEmpty())
            @include('courses.partials.curriculum-sidebar', [
                'course' => $course,
                'completedLessonIds' => $completedLessonIds,
                'completedCount' => $completedCount,
                'totalLessons' => $totalLessons,
                'sidebarClass' => 'order-first',
            ])
        @endif

        <div class="flex-1 min-w-0">
            <section class="relative overflow-hidden bg-navy text-white">
                <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14">
                    <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                        <ol class="flex flex-wrap items-center gap-2">
                            <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('courses.index') }}" class="hover:text-white transition">Courses</a></li>
                            @if($course->category)
                                <li aria-hidden="true">/</li>
                                <li>
                                    <a href="{{ route('courses.index', ['category' => $course->category->slug]) }}" class="hover:text-white transition">
                                        {{ $course->category->name }}
                                    </a>
                                </li>
                            @endif
                            <li aria-hidden="true">/</li>
                            <li class="text-white font-medium truncate max-w-[14rem] sm:max-w-[20rem]">{{ $course->title }}</li>
                        </ol>
                    </nav>

                    @if(session('success'))
                        <div class="mb-6 p-4 rounded-xl bg-success-light text-success-darker border border-success-muted">{{ session('success') }}</div>
                    @endif
                    @if(session('info'))
                        <div class="mb-6 p-4 rounded-xl bg-accent-light text-accent-darker border border-accent-muted">{{ session('info') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
                    @endif

                    <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                        <div class="lg:col-span-7">
                            @if($course->category)
                                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-accent">{{ $course->category->name }}</p>
                            @endif
                            <h1 class="mt-3 font-display font-bold text-3xl sm:text-4xl tracking-tight">{{ $course->title }}</h1>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 text-sm text-slate-200">
                                    <svg class="h-4 w-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    {{ $totalLessons }} {{ \Illuminate\Support\Str::plural('lesson', $totalLessons) }}
                                </span>
                                @if($course->duration)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 text-sm text-slate-200">
                                        <svg class="h-4 w-4 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $course->duration }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 text-sm text-slate-200">Self-paced</span>
                                @endif
                                @if(($course->price ?? 0) > 0)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-accent text-white text-sm font-semibold">${{ number_format($course->price, 0) }}</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-success text-white text-sm font-semibold">Free</span>
                                @endif
                            </div>

                            @if($course->instructor)
                                <p class="mt-4 text-sm text-slate-300">
                                    Instructor
                                    <span class="text-white font-medium">{{ $course->instructor->name }}</span>
                                </p>
                            @endif

                            <div class="mt-8 flex flex-wrap gap-3 items-center">
                                @if($enrolled)
                                    @if($totalLessons > 0)
                                        <div class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 text-white text-sm">
                                            <span class="font-medium">Progress</span>
                                            <span class="tabular-nums">{{ $completedCount }} / {{ $totalLessons }}</span>
                                        </div>
                                    @endif
                                    @if($resumeLesson)
                                        <a href="{{ route('courses.lessons.show', [$course, $resumeLesson]) }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                            {{ $completedCount > 0 ? 'Resume course' : 'Start course' }}
                                        </a>
                                    @elseif($totalLessons > 0)
                                        <span class="inline-flex items-center px-4 py-2.5 rounded-xl bg-success text-white text-sm font-medium">Course completed</span>
                                    @endif
                                    <a href="{{ route('courses.my-courses') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl border border-white/25 text-white hover:bg-white/10 font-medium text-sm transition">My Learning</a>
                                @else
                                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                                            Enroll in this course
                                        </button>
                                    </form>
                                    @guest
                                        <p class="text-sm text-slate-400 self-center">Sign in to enroll and track your progress.</p>
                                    @endguest
                                @endif
                            </div>
                        </div>

                        <div class="lg:col-span-5">
                            <div class="rounded-2xl overflow-hidden ring-1 ring-white/10 shadow-brand aspect-[16/10] bg-white/5">
                                @if($course->banner_url)
                                    <img src="{{ $course->banner_url }}" alt="" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-accent">
                                        @include('partials.category-icon', [
                                            'slug' => $course->category->slug ?? 'book',
                                            'name' => $course->category->name ?? '',
                                            'class' => 'h-14 w-14',
                                        ])
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="py-12 lg:py-16 bg-slate-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid lg:grid-cols-12 gap-10 lg:gap-12">
                        <div class="lg:col-span-5">
                            <h2 class="font-display font-bold text-xl text-navy">About this course</h2>
                            @if(filled(trim((string) $course->description)))
                                <div class="mt-4 text-slate-600 leading-relaxed whitespace-pre-wrap">{{ $course->description }}</div>
                            @else
                                <p class="mt-4 text-slate-500 leading-relaxed">
                                    Course details will be added soon. Browse the curriculum below to see what’s included.
                                </p>
                            @endif
                        </div>

                        <div class="lg:col-span-7">
                            <div class="flex items-end justify-between gap-4 mb-5">
                                <h2 class="font-display font-bold text-xl text-navy">Curriculum</h2>
                                @if($totalLessons > 0)
                                    <p class="text-sm text-slate-500 tabular-nums">{{ $totalLessons }} {{ \Illuminate\Support\Str::plural('lesson', $totalLessons) }}</p>
                                @endif
                            </div>

                            @if($course->chapters->isEmpty())
                                <div class="rounded-2xl border border-slate-200 bg-white px-5 py-8 text-center">
                                    <p class="text-slate-500">Curriculum will be added soon.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($course->chapters as $chapterIndex => $chapter)
                                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                                            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center gap-3">
                                                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-light text-primary text-xs font-semibold flex items-center justify-center tabular-nums">{{ $chapterIndex + 1 }}</span>
                                                <h3 class="font-display font-semibold text-navy">{{ $chapter->title }}</h3>
                                            </div>
                                            <ul class="divide-y divide-slate-100">
                                                @forelse($chapter->lessons as $lessonIndex => $lesson)
                                                    <li>
                                                        @if($enrolled)
                                                            <a href="{{ route('courses.lessons.show', [$course, $lesson]) }}" class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition group">
                                                                <span class="flex-shrink-0 w-6 text-xs tabular-nums text-slate-400">{{ $lessonIndex + 1 }}</span>
                                                                <span class="flex-1 min-w-0 font-medium text-slate-700 group-hover:text-primary transition">{{ $lesson->title }}</span>
                                                                @if($completedLessonIds->contains($lesson->id))
                                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-success-light text-success-dark" aria-label="Completed">
                                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                                    </span>
                                                                @else
                                                                    <svg class="h-4 w-4 text-slate-300 group-hover:text-primary transition" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                                                @endif
                                                            </a>
                                                        @else
                                                            <div class="flex items-center gap-3 px-5 py-3.5">
                                                                <span class="flex-shrink-0 w-6 text-xs tabular-nums text-slate-400">{{ $lessonIndex + 1 }}</span>
                                                                <span class="flex-1 min-w-0 font-medium text-slate-700">{{ $lesson->title }}</span>
                                                                <span class="text-xs text-slate-400">Enroll to access</span>
                                                            </div>
                                                        @endif
                                                    </li>
                                                @empty
                                                    <li class="px-5 py-4 text-sm text-slate-500">No lessons in this chapter yet.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if(($upcomingLiveSessions ?? collect())->isNotEmpty())
                                <div class="mt-10">
                                    <h2 class="font-display font-bold text-xl text-navy mb-5">Upcoming live sessions</h2>
                                    <div class="space-y-3">
                                        @foreach($upcomingLiveSessions as $session)
                                            <div class="rounded-2xl border border-slate-200 bg-white p-5 flex flex-wrap items-center justify-between gap-4">
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-navy">{{ $session->title }}</p>
                                                    <p class="text-sm text-slate-500 mt-1">{{ $session->scheduled_at->format('l, M j, Y \a\t g:i A') }} · {{ $session->duration_minutes }} min</p>
                                                    @if($session->description)
                                                        <p class="text-sm text-slate-600 mt-2">{{ \Illuminate\Support\Str::limit($session->description, 120) }}</p>
                                                    @endif
                                                </div>
                                                @php $isInvited = auth()->check() && $session->invitedAttendees->contains('id', auth()->id()); @endphp
                                                @if($isInvited)
                                                    <div class="flex flex-col items-end gap-1">
                                                        <a href="{{ $session->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">Join session</a>
                                                        @if($session->meeting_password)
                                                            <span class="text-xs text-slate-500">Password: {{ $session->meeting_password }}</span>
                                                        @endif
                                                    </div>
                                                @elseif($enrolled)
                                                    <p class="text-sm text-slate-500">You were not invited to this session.</p>
                                                @else
                                                    <p class="text-sm text-slate-500">Enroll to be eligible for live sessions.</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($enrolled && $course->quizzes->isNotEmpty())
                                <div class="mt-10">
                                    <h2 class="font-display font-bold text-xl text-navy mb-5">Quizzes</h2>
                                    <div class="space-y-3">
                                        @foreach($course->quizzes as $quiz)
                                            <a href="{{ route('courses.quizzes.show', [$course, $quiz]) }}" class="block rounded-2xl border border-slate-200 bg-white p-5 hover:border-primary-muted hover:shadow-brand transition">
                                                <span class="font-semibold text-navy">{{ $quiz->title }}</span>
                                                <p class="text-sm text-slate-500 mt-1">Passing grade: {{ $quiz->passing_grade }}% · {{ $quiz->questions_count ?? 0 }} questions</p>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
