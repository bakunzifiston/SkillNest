@extends('layouts.site')

@section('title', 'Quiz: ' . $quizAttempt->quiz->title)

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <p class="text-sm text-slate-500">Quiz: {{ $quizAttempt->quiz->title }} ({{ $quizAttempt->quiz->course->title }})</p>
        <h1 class="font-display font-bold text-2xl text-slate-900 mt-2">Answer the questions</h1>
        <form action="{{ route('quizzes.submit', $quizAttempt) }}" method="post" class="mt-8 space-y-8">
            @csrf
            @foreach($quizAttempt->quiz->questions as $index => $q)
                <div class="bg-white rounded-xl border border-slate-200 p-6">
                    <p class="font-semibold text-slate-900">{{ $index + 1 }}. {{ $q->question_text }}</p>
                    <div class="mt-4 space-y-2">
                        @foreach($q->options as $opt)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 cursor-pointer">
                                <input type="radio" name="question_{{ $q->id }}" value="{{ $opt->id }}" class="text-amber-600 focus:ring-amber-500" required>
                                <span class="text-slate-700">{{ $opt->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="flex gap-4">
                <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                    Submit quiz
                </button>
                <a href="{{ route('courses.show', $quizAttempt->quiz->course) }}" class="inline-flex items-center px-4 py-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-medium">Cancel</a>
            </div>
        </form>
    </div>
@endsection
