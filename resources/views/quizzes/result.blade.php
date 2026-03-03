@extends('layouts.site')

@section('title', 'Quiz result')

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="mb-6 p-4 rounded-xl bg-amber-50 text-amber-800 border border-amber-200">{{ session('info') }}</div>
        @endif
        <a href="{{ route('courses.show', $quizAttempt->quiz->course) }}" class="text-amber-600 hover:underline text-sm font-medium">← Back to course</a>
        <div class="mt-6 bg-white rounded-2xl border border-slate-200 p-8">
            <h1 class="font-display font-bold text-2xl text-slate-900">Quiz result</h1>
            <p class="text-slate-600 mt-1">{{ $quizAttempt->quiz->title }}</p>
            <div class="mt-6 flex items-center gap-4">
                <span class="text-3xl font-bold text-slate-900">{{ $quizAttempt->score }} / {{ $quizAttempt->total_points }}</span>
                <span class="text-lg text-slate-600">({{ $quizAttempt->percentage !== null ? round($quizAttempt->percentage, 1) : 0 }}%)</span>
                @if($quizAttempt->passed)
                    <span class="px-4 py-2 rounded-xl bg-emerald-100 text-emerald-800 font-semibold">Passed</span>
                @else
                    <span class="px-4 py-2 rounded-xl bg-red-100 text-red-800 font-semibold">Not passed</span>
                @endif
            </div>
            <p class="mt-4 text-sm text-slate-500">Passing grade: {{ $quizAttempt->quiz->passing_grade }}%</p>
        </div>
        <div class="mt-8 bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <h2 class="px-6 py-4 border-b border-slate-200 font-display font-semibold text-slate-900">Your answers</h2>
            <ul class="divide-y divide-slate-100">
                @foreach($quizAttempt->quiz->questions as $q)
                    @php $answer = $quizAttempt->answers->firstWhere('question_id', $q->id); @endphp
                    <li class="px-6 py-4">
                        <p class="font-medium text-slate-900">{{ $q->question_text }}</p>
                        <p class="text-sm mt-1">
                            @if($answer && $answer->questionOption)
                                You chose: <strong>{{ $answer->questionOption->option_text }}</strong>
                                @if($answer->is_correct)
                                    <span class="text-emerald-600">✓ Correct</span>
                                @else
                                    <span class="text-red-600">✗ Incorrect</span>
                                @endif
                            @else
                                <span class="text-slate-400">No answer</span>
                            @endif
                        </p>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="mt-6">
            <a href="{{ route('courses.show', $quizAttempt->quiz->course) }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Back to course</a>
        </div>
    </div>
@endsection
