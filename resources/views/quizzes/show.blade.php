@extends('layouts.site')

@section('title', $quiz->tr('title'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200" role="alert">{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="mb-6 p-4 rounded-xl bg-accent-light text-accent-darker border border-accent-muted">{{ session('info') }}</div>
        @endif
        <a href="{{ route('courses.show', $course) }}" class="text-primary hover:underline text-sm font-medium">← {{ __('Back to course') }}</a>
        <div class="mt-6 bg-white rounded-2xl border border-slate-200 p-8">
            <h1 class="font-display font-bold text-2xl text-slate-900">{{ $quiz->tr('title') }}</h1>
            <p class="text-slate-600 mt-2">{{ $course->tr('title') }}</p>
            @if($quiz->tr('description'))
                <div class="mt-4 text-slate-600">{{ $quiz->tr('description') }}</div>
            @endif
            <ul class="mt-6 space-y-2 text-sm text-slate-600">
                <li>{{ __('Questions: :count', ['count' => $quiz->questions->count()]) }}</li>
                <li>{{ __('Passing grade: :grade%', ['grade' => $quiz->passing_grade]) }}</li>
                @if($quiz->time_limit_minutes)
                    <li>{{ __('Time limit: :count minutes', ['count' => $quiz->time_limit_minutes]) }}</li>
                @endif
                <li>
                    @if($state['max'] !== null)
                        {{ __('Attempts: :used of :max used', ['used' => $state['used'], 'max' => $state['max']]) }}
                    @else
                        {{ __('Attempts: :used used (no limit)', ['used' => $state['used']]) }}
                    @endif
                </li>
                @unless($quiz->allow_retake_after_pass)
                    <li>{{ __('You cannot retake this quiz after passing it.') }}</li>
                @endunless
            </ul>

            @if($state['unfinished'])
                <p class="mt-6 text-sm text-slate-600">{{ __('You have an unfinished attempt. Pick up where you left off.') }}</p>
            @elseif($state['passed'] && $state['canStart'])
                <p class="mt-6 p-3 rounded-xl bg-success-light text-success-darker text-sm font-medium">{{ __('You passed this quiz.') }}</p>
            @endif

            @if($state['canStart'])
                <form action="{{ route('courses.quizzes.start', [$course, $quiz]) }}" method="post" class="mt-6">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">
                        @if($state['unfinished']) {{ __('Resume quiz') }}
                        @elseif($state['used'] > 0) {{ __('Retake quiz') }}
                        @else {{ __('Start quiz') }}
                        @endif
                    </button>
                </form>
            @else
                <p class="mt-6 p-3 rounded-xl bg-slate-100 text-slate-700 text-sm font-medium" role="status">
                    @if($state['reason'] === 'passed')
                        {{ __('You already passed this quiz and cannot retake it.') }}
                    @else
                        {{ __('You have used all :max attempts for this quiz.', ['max' => $state['max']]) }}
                    @endif
                </p>
            @endif

            @if($state['last'])
                <a href="{{ route('quizzes.result', $state['last']) }}" class="mt-4 inline-flex text-sm font-semibold text-primary hover:text-accent-dark">{{ __('View your last result') }} →</a>
            @endif
        </div>
    </div>
@endsection
