@extends('layouts.admin')

@section('title', 'Edit Question')
@section('header', 'Edit Question')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.questions.update', $question) }}" method="post" class="space-y-5" id="question-form">
            @csrf
            @method('PUT')
            <p class="text-sm text-gray-500">Type: {{ $question->type === 'mcq' ? 'MCQ' : 'True/False' }} (cannot change)</p>
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">Question text</label>
                <textarea name="question_text" id="question_text" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700">Points</label>
                <input type="number" name="points" id="points" value="{{ old('points', $question->points) }}" min="1" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('points')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            @if($question->type === 'true_false')
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">Correct answer</p>
                @php $opts = $question->options->sortBy('sort_order'); $correctTrue = $opts->firstWhere('option_text', 'True')?->is_correct ?? true; @endphp
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="true" {{ old('true_false_correct', $correctTrue ? 'true' : 'false') === 'true' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <span>True</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="false" {{ old('true_false_correct', $correctTrue ? 'true' : 'false') === 'false' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <span>False</span>
                </label>
            </div>
            @else
            <div class="space-y-3">
                <p class="text-sm font-medium text-gray-700">Options (select correct one)</p>
                @foreach($question->options as $i => $opt)
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_option" value="{{ $i }}" {{ $opt->is_correct ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <input type="hidden" name="option_ids[]" value="{{ $opt->id }}">
                    <input type="text" name="option_texts[]" value="{{ old('option_texts.'.$i, $opt->option_text) }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                @endforeach
                <p class="text-sm text-gray-500 mt-2">Add more options (optional):</p>
                <input type="text" name="new_options[]" placeholder="New option" class="rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 w-full">
                <input type="text" name="new_options[]" placeholder="New option" class="rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 w-full">
            </div>
            @endif

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Save</button>
                <a href="{{ route('admin.quizzes.questions.index', $question->quiz) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
