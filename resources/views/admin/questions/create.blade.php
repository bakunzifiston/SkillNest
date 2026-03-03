@extends('layouts.admin')

@section('title', 'Add Question')
@section('header', 'Add Question: ' . $quiz->title)

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.quizzes.questions.store', $quiz) }}" method="post" class="space-y-5" id="question-form">
            @csrf
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                <select name="type" id="type" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="mcq" {{ old('type') === 'mcq' ? 'selected' : '' }}>Multiple choice (MCQ)</option>
                    <option value="true_false" {{ old('type') === 'true_false' ? 'selected' : '' }}>True / False</option>
                </select>
                @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">Question text</label>
                <textarea name="question_text" id="question_text" rows="3" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('question_text') }}</textarea>
                @error('question_text')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="points" class="block text-sm font-medium text-gray-700">Points</label>
                <input type="number" name="points" id="points" value="{{ old('points', 1) }}" min="1" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('points')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="true-false-options" class="space-y-3 hidden">
                <p class="text-sm font-medium text-gray-700">Correct answer</p>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="true" {{ old('true_false_correct') === 'true' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <span>True</span>
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="true_false_correct" value="false" {{ old('true_false_correct') === 'false' ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <span>False</span>
                </label>
            </div>

            <div id="mcq-options" class="space-y-3">
                <p class="text-sm font-medium text-gray-700">Options (select correct one)</p>
                @for($i = 0; $i < 4; $i++)
                <div class="flex items-center gap-2">
                    <input type="radio" name="correct_option" value="{{ $i }}" {{ old('correct_option', 0) == $i ? 'checked' : '' }} class="text-amber-600 focus:ring-amber-500">
                    <input type="text" name="options[]" value="{{ old('options.'.$i) }}" placeholder="Option {{ $i + 1 }}" class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                @endfor
                @error('options')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add question</button>
                <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
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
