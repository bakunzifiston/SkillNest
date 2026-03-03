@extends('layouts.admin')

@section('title', 'Quiz Attempt')
@section('header', 'Quiz attempt: ' . ($quizAttempt->user->name ?? 'Student'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.quiz-results.index') }}" class="text-amber-600 hover:underline">← Back to results</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">Student</dt>
                <dd class="font-medium">{{ $quizAttempt->user->name ?? '—' }} ({{ $quizAttempt->user->email ?? '' }})</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Course / Quiz</dt>
                <dd class="font-medium">{{ $quizAttempt->quiz->course->title ?? '—' }} — {{ $quizAttempt->quiz->title }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Score</dt>
                <dd class="font-medium">{{ $quizAttempt->score }} / {{ $quizAttempt->total_points }} ({{ $quizAttempt->percentage !== null ? round($quizAttempt->percentage, 1) : 0 }}%)</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Result</dt>
                <dd>
                    @if($quizAttempt->passed)
                        <span class="text-green-600 font-medium">Passed</span>
                    @else
                        <span class="text-red-600">Failed</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">Submitted at</dt>
                <dd>{{ $quizAttempt->submitted_at?->format('M j, Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <h3 class="px-6 py-3 bg-gray-50 font-medium text-gray-800">Answers</h3>
        <ul class="divide-y divide-gray-200">
            @foreach($quizAttempt->quiz->questions as $q)
                @php $answer = $quizAttempt->answers->firstWhere('question_id', $q->id); @endphp
                <li class="px-6 py-4">
                    <p class="font-medium text-gray-900">{{ $q->question_text }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        @if($answer)
                            Selected: {{ $answer->questionOption->option_text ?? '—' }}
                            @if($answer->is_correct)
                                <span class="text-green-600">✓ Correct</span>
                            @else
                                <span class="text-red-600">✗ Incorrect</span>
                            @endif
                            ({{ $answer->points_earned }} pts)
                        @else
                            <span class="text-gray-400">No answer</span>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
