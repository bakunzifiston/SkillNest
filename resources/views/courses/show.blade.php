@extends('layouts.site')

@section('title', $course->title)

@section('content')
    <div class="flex flex-col lg:flex-row">
        @if($enrolled && $course->chapters->isNotEmpty())
        {{-- Sidebar: chapters & lessons (for enrolled learners) --}}
        <aside class="lg:w-72 lg:min-w-[18rem] flex-shrink-0 bg-white border-b lg:border-b-0 lg:border-r border-slate-200 order-first">
            <div class="p-4 border-b border-slate-200 bg-slate-50">
                <h2 class="font-display font-semibold text-slate-900 text-sm">Curriculum</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $completedCount }} / {{ $totalLessons }} lessons</p>
            </div>
            <nav class="py-3 overflow-y-auto max-h-[50vh] lg:max-h-[70vh]" aria-label="Course curriculum">
                @foreach($course->chapters as $chapter)
                    <div class="mb-2">
                        <div class="px-4 py-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $chapter->title }}</div>
                        <ul class="space-y-0.5">
                            @foreach($chapter->lessons as $l)
                                <li>
                                    <a href="{{ route('courses.lessons.show', [$course, $l]) }}" class="flex items-center gap-2 py-2 px-4 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-800 border-l-2 border-transparent hover:border-amber-300">
                                        <span class="flex-1 min-w-0 truncate">{{ $l->title }}</span>
                                        @if($completedLessonIds->contains($l->id))
                                            <span class="flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">✓</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>
        @endif
        <div class="flex-1 min-w-0">
    <section class="bg-white border-b border-slate-200 py-8 lg:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-6 p-4 rounded-xl bg-amber-50 text-amber-800 border border-amber-200">{{ session('info') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-800 border border-red-200">{{ session('error') }}</div>
            @endif

            @if($course->category ?? null)
                <span class="text-sm font-medium text-amber-600 uppercase tracking-wide">{{ $course->category->name }}</span>
            @endif
            <h1 class="mt-2 font-display font-bold text-3xl lg:text-4xl text-slate-900">{{ $course->title }}</h1>
            @if($course->instructor ?? null)
                <p class="mt-2 text-slate-600">By {{ $course->instructor->name }}</p>
            @endif
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-500">
                <span>{{ $course->duration ?? 'Self-paced' }}</span>
                <span>{{ $totalLessons }} {{ Str::plural('lesson', $totalLessons) }}</span>
                @if(($course->price ?? 0) > 0)
                    <span class="font-semibold text-amber-600">${{ number_format($course->price, 0) }}</span>
                @else
                    <span class="font-semibold text-amber-600">Free</span>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap gap-4">
                @if($enrolled)
                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700">
                            <span class="font-medium">Progress:</span>
                            <span>{{ $completedCount }} / {{ $totalLessons }} lessons</span>
                        </div>
                        @if($resumeLesson)
                            <a href="{{ route('courses.lessons.show', [$course, $resumeLesson]) }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                                {{ $completedCount > 0 ? 'Resume course' : 'Start course' }}
                            </a>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 font-medium">Course completed</span>
                        @endif
                        <a href="{{ route('courses.my-courses') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">My courses</a>
                    </div>
                @else
                    <form action="{{ route('courses.enroll', $course) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                            Enroll in this course
                        </button>
                    </form>
                    <p class="text-sm text-slate-500 self-center">Sign in or create an account to enroll and track your progress.</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 lg:py-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($course->banner_url)
                <div class="rounded-2xl overflow-hidden border border-slate-200 mb-10">
                    <img src="{{ $course->banner_url }}" alt="" class="w-full aspect-video object-cover">
                </div>
            @endif

            <div class="prose prose-slate max-w-none">
                <h2 class="font-display font-bold text-xl text-slate-900 mb-4">About this course</h2>
                <div class="text-slate-600 whitespace-pre-wrap">{{ $course->description ?? 'No description.' }}</div>
            </div>

            <div class="mt-14">
                <h2 class="font-display font-bold text-xl text-slate-900 mb-6">Curriculum</h2>
                <div class="space-y-6">
                    @foreach($course->chapters as $chapter)
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white">
                            <div class="px-5 py-4 bg-slate-50 border-b border-slate-200 font-display font-semibold text-slate-900">
                                {{ $chapter->title }}
                            </div>
                            <ul class="divide-y divide-slate-100">
                                @foreach($chapter->lessons as $lesson)
                                    <li class="flex items-center gap-3 px-5 py-3">
                                        <a href="{{ route('courses.lessons.show', [$course, $lesson]) }}" class="flex-1 {{ $enrolled ? 'text-slate-700 hover:text-amber-600' : 'text-slate-600 hover:text-amber-600' }} font-medium">
                                            {{ $lesson->title }}
                                        </a>
                                        @if($enrolled && $completedLessonIds->contains($lesson->id))
                                            <span class="text-emerald-600 text-sm font-medium" aria-label="Completed">✓</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
                @if($course->chapters->isEmpty())
                    <p class="text-slate-500 py-6">Curriculum will be added soon.</p>
                @endif
            </div>

            @if(($upcomingLiveSessions ?? collect())->isNotEmpty())
            <div class="mt-14">
                <h2 class="font-display font-bold text-xl text-slate-900 mb-6">Upcoming live sessions</h2>
                <div class="space-y-4">
                    @foreach($upcomingLiveSessions as $session)
                        <div class="border border-slate-200 rounded-xl p-5 bg-white flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <span class="font-semibold text-slate-900">{{ $session->title }}</span>
                                <p class="text-sm text-slate-500 mt-1">{{ $session->scheduled_at->format('l, M j, Y \a\t g:i A') }} · {{ $session->duration_minutes }} min</p>
                                @if($session->description)
                                    <p class="text-sm text-slate-600 mt-2">{{ Str::limit($session->description, 120) }}</p>
                                @endif
                            </div>
                            @php $isInvited = auth()->check() && $session->invitedAttendees->contains('id', auth()->id()); @endphp
                            @if($isInvited)
                                <div class="flex flex-col items-end gap-1">
                                    <a href="{{ $session->meeting_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Join session</a>
                                    @if($session->meeting_password)
                                        <span class="text-xs text-slate-500">Password: {{ $session->meeting_password }}</span>
                                    @endif
                                </div>
                            @elseif($enrolled)
                                <p class="text-sm text-slate-500">You were not invited to this session.</p>
                            @else
                                <p class="text-sm text-slate-500">Enroll in this course to be eligible for live sessions.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($enrolled && $course->quizzes->isNotEmpty())
            <div class="mt-14">
                <h2 class="font-display font-bold text-xl text-slate-900 mb-6">Quizzes</h2>
                <div class="space-y-4">
                    @foreach($course->quizzes as $quiz)
                        <a href="{{ route('courses.quizzes.show', [$course, $quiz]) }}" class="block border border-slate-200 rounded-xl p-5 bg-white hover:border-amber-300 hover:shadow-md transition">
                            <span class="font-semibold text-slate-900">{{ $quiz->title }}</span>
                            <p class="text-sm text-slate-500 mt-1">Passing grade: {{ $quiz->passing_grade }}% · {{ $quiz->questions_count ?? 0 }} questions</p>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </section>
        </div>
    </div>
@endsection
