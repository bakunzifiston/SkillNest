@extends('layouts.admin')

@section('title', __('Quiz Attempt'))
@section('header', __('Quiz attempt: :name', ['name' => $quizAttempt->user->name ?? __('Student')]))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.quiz-results.index') }}" class="admin-btn-secondary">{{ __('Back to results') }}</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm text-gray-500">{{ __('Student') }}</dt>
                <dd class="font-medium">{{ $quizAttempt->user->name ?? '—' }} ({{ $quizAttempt->user->email ?? '' }})</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">{{ __('Course / Quiz') }}</dt>
                <dd class="font-medium">{{ $quizAttempt->quiz->course->title ?? '—' }} — {{ $quizAttempt->quiz->title }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">{{ __('Score') }}</dt>
                <dd class="font-medium">{{ $quizAttempt->score }} / {{ $quizAttempt->total_points }} ({{ $quizAttempt->percentage !== null ? round($quizAttempt->percentage, 1) : 0 }}%)</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">{{ __('Result') }}</dt>
                <dd>
                    @if($quizAttempt->passed)
                        <span class="text-success-dark font-medium">{{ __('Passed') }}</span>
                    @else
                        <span class="text-red-600">{{ __('Failed') }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm text-gray-500">{{ __('Submitted at') }}</dt>
                <dd>{{ $quizAttempt->submitted_at?->translatedFormat('M j, Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <h3 class="px-6 py-3 bg-gray-50 font-medium text-gray-800">{{ __('Answers') }}</h3>
        <ul class="divide-y divide-gray-200">
            @foreach($quizAttempt->quiz->questions as $q)
                @php $answer = $quizAttempt->answers->firstWhere('question_id', $q->id); @endphp
                <li class="px-6 py-4">
                    <p class="font-medium text-gray-900">{{ $q->question_text }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        @if($answer)
                            {{ __('Selected: :answer', ['answer' => $answer->questionOption->option_text ?? '—']) }}
                            @if($answer->is_correct)
                                <span class="text-success-dark">✓ {{ __('Correct') }}</span>
                            @else
                                <span class="text-red-600">✗ {{ __('Incorrect') }}</span>
                            @endif
                            ({{ __(':count pts', ['count' => $answer->points_earned]) }})
                        @else
                            <span class="text-gray-400">{{ __('No answer') }}</span>
                        @endif
                    </p>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
