@extends('layouts.admin')

@section('title', __('Edit Question'))
@section('header', __('Edit Question'))

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.questions.update', $question) }}" method="post" class="space-y-5" id="question-form">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-500">{{ __('Type: :type (cannot change)', ['type' => $question->type === 'mcq' ? __('MCQ') : __('True/False')]) }}</p>
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">{{ __('Question text') }}</label>
                <textarea name="question_text" id="question_text" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700">{{ __('Points') }}</label>
                <input type="number" name="points" id="points" value="{{ old('points', $question->points) }}" min="1" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('points')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if($question->type === 'true_false')
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">{{ __('Correct answer') }}</p>
                @php $opts = $question->options->sortBy('sort_order'); $correctTrue = $opts->firstWhere('option_text', 'True')?->is_correct ?? true; @endphp
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="true" {{ old('true_false_correct', $correctTrue ? 'true' : 'false') === 'true' ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <span>{{ __('True') }}</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="false" {{ old('true_false_correct', $correctTrue ? 'true' : 'false') === 'false' ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <span>{{ __('False') }}</span>
                </label>
            </div>
            @else
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">{{ __('Options (select correct one)') }}</p>
                @foreach($question->options as $i => $opt)
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_option" value="{{ $i }}" {{ $opt->is_correct ? 'checked' : '' }} class="text-primary focus:ring-accent">
                    <input type="hidden" name="option_ids[]" value="{{ $opt->id }}">
                    <input type="text" name="option_texts[]" value="{{ old('option_texts.'.$i, $opt->option_text) }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                </div>
                @endforeach
                <p class="text-sm text-gray-500 mt-2">{{ __('Add more options (optional):') }}</p>
                <input type="text" name="new_options[]" placeholder="{{ __('New option') }}" class="rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent w-full">
                <input type="text" name="new_options[]" placeholder="{{ __('New option') }}" class="rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent w-full">
            </div>
            @endif

            @include('admin.partials.translation-fields', [
                'model' => $question,
                'fields' => [
                    'question_text' => ['label' => __('Question text'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            @if($question->type !== 'true_false')
            @foreach(collect(config('localization.locales'))->except(config('localization.default')) as $code => $meta)
            <details class="rounded-xl border border-slate-200 bg-slate-50/70 p-4" @if($question->options->contains(fn ($o) => $o->isTranslatedTo($code)) || collect(old('tr_options', []))->filter()->isNotEmpty()) open @endif>
                <summary class="cursor-pointer select-none text-sm font-semibold text-navy">{{ __(':language translation of the options (optional)', ['language' => $meta['name']]) }}</summary>
                <div class="mt-3 space-y-2">
                    @foreach($question->options as $i => $opt)
                    <input type="text" name="tr_options[{{ $opt->id }}]" value="{{ old('tr_options.'.$opt->id, $opt->translationFor('option_text', $code)) }}" lang="{{ $code }}" placeholder="{{ $opt->option_text }}" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    @endforeach
                    <p class="text-xs text-gray-500">{{ __('Translations of the new options above can be added after saving.') }}</p>
                </div>
            </details>
            @endforeach
            @endif

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">{{ __('Save') }}</button>
                <a href="{{ route('admin.quizzes.questions.index', $question->quiz) }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
