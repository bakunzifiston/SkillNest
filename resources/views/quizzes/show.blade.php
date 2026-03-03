@extends('layouts.site')

@section('title', $quiz->title)

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <a href="{{ route('courses.show', $course) }}" class="text-amber-600 hover:underline text-sm font-medium">← Back to course</a>
        <div class="mt-6 bg-white rounded-2xl border border-slate-200 p-8">
            <h1 class="font-display font-bold text-2xl text-slate-900">{{ $quiz->title }}</h1>
            <p class="text-slate-600 mt-2">{{ $course->title }}</p>
            @if($quiz->description)
                <div class="mt-4 text-slate-600">{{ $quiz->description }}</div>
            @endif
            <ul class="mt-6 space-y-2 text-sm text-slate-600">
                <li>Questions: {{ $quiz->questions->count() }}</li>
                <li>Passing grade: {{ $quiz->passing_grade }}%</li>
                @if($quiz->time_limit_minutes)
                    <li>Time limit: {{ $quiz->time_limit_minutes }} minutes</li>
                @endif
            </ul>
            <form action="{{ route('courses.quizzes.start', [$course, $quiz]) }}" method="post" class="mt-8">
                @csrf
                <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">
                    Start quiz
                </button>
            </form>
        </div>
    </div>
@endsection
