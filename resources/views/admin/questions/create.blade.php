@extends('layouts.admin')

@section('title', __('Add Question'))
@section('header', __('Add Question: :title', ['title' => $quiz->title]))

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.quizzes.questions.store', $quiz) }}" method="post" class="space-y-5" id="question-form">
            @csrf
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
                <select name="type" id="type" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    <option value="mcq" {{ old('type') === 'mcq' ? 'selected' : '' }}>{{ __('Multiple choice (MCQ)') }}</option>
                    <option value="true_false" {{ old('type') === 'true_false' ? 'selected' : '' }}>{{ __('True / False') }}</option>
                </select>
                @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">{{ __('Question text') }}</label>
                <textarea name="question_text" id="question_text" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">{{ old('question_text') }}</textarea>
                @error('question_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700">{{ __('Points') }}</label>
                <input type="number" name="points" id="points" value="{{ old('points', 1) }}" min="1" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('points')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="true-false-options" class="space-y-3 hidden">
                <p class="text-sm font-medium text-gray-700">{{ __('Correct answer') }}</p>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="true" {{ old('true_false_correct') === 'true' ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <span>{{ __('True') }}</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="false" {{ old('true_false_correct') === 'false' ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <span>{{ __('False') }}</span>
                </label>
            </div>

            <div id="mcq-options" class="space-y-3">
                <p class="text-sm font-medium text-gray-700">{{ __('Options (select correct one)') }}</p>
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_option" value="{{ $i }}" {{ old('correct_option', 0) == $i ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <input type="text" name="options[]" value="{{ old('options.'.$i) }}" placeholder="{{ __('Option :number', ['number' => $i + 1]) }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                </div>
                @endfor
                @error('options')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                @foreach(collect(config('localization.locales'))->except(config('localization.default')) as $code => $meta)
                <details class="rounded-xl border border-slate-200 bg-slate-50/70 p-4" @if(collect(old('tr_new_options', []))->filter()->isNotEmpty()) open @endif>
                    <summary class="cursor-pointer select-none text-sm font-semibold text-navy">{{ __(':language translation of the options (optional)', ['language' => $meta['name']]) }}</summary>
                    <div class="mt-3 space-y-2">
                        @for($i = 0; $i < 4; $i++)
                        <input type="text" name="tr_new_options[]" value="{{ old('tr_new_options.'.$i) }}" lang="{{ $code }}" placeholder="{{ __('Option :number', ['number' => $i + 1]) }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                        @endfor
                    </div>
                </details>
                @endforeach
            </div>

            @include('admin.partials.translation-fields', [
                'model' => null,
                'fields' => [
                    'question_text' => ['label' => __('Question text'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">{{ __('Add question') }}</button>
                <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
    <script>
        document.getElementById('type').addEventListener('change', function () {
            var isTf = this.value === 'true_false';
            document.getElementById('true-false-options').classList.toggle('hidden', !isTf);
            document.getElementById('mcq-options').classList.toggle('hidden', isTf);
        });
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('type').dispatchEvent(new Event('change'));
        });
    </script>
@endsection
