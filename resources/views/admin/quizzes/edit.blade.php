@extends('layouts.admin')

@section('title', __('Edit Quiz'))
@section('header', __('Edit Quiz: :title', ['title' => $quiz->title]))

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.quizzes.update', $quiz) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <p class="text-sm text-gray-500">{{ __('Course: :title', ['title' => $quiz->course->title]) }}</p>
            </div>
            @include('admin.quizzes.partials.placement', ['courseLocked' => true, 'selectedCourseId' => $quiz->course_id, 'selectedChapterId' => $quiz->chapter_id, 'selectedLessonId' => $quiz->lesson_id])
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title', $quiz->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description (optional)') }}</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">{{ old('description', $quiz->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="passing_grade" class="block text-sm font-medium text-gray-700">{{ __('Passing grade (%)') }}</label>
                <input type="number" name="passing_grade" id="passing_grade" value="{{ old('passing_grade', $quiz->passing_grade) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('passing_grade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700">{{ __('Time limit (minutes, optional)') }}</label>
                <input type="number" name="time_limit_minutes" id="time_limit_minutes" value="{{ old('time_limit_minutes', $quiz->time_limit_minutes) }}" min="1" max="300" placeholder="{{ __('No limit') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('time_limit_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ (bool) old('is_published', $quiz->is_published) ? 'checked' : '' }} class="rounded border-gray-300 text-primary focus:ring-accent">
                    <span class="text-sm text-gray-700">{{ __('Published') }}</span>
                </label>
            </div>
            @include('admin.partials.translation-fields', [
                'model' => $quiz,
                'fields' => [
                    'title' => ['label' => __('Title'), 'type' => 'input'],
                    'description' => ['label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            @include('admin.partials.translation-fields', [
                'model' => $quiz,
                'fields' => [
                    'title' => ['label' => __('Title'), 'type' => 'input'],
                    'description' => ['label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">{{ __('Save') }}</button>
                <a href="{{ route('admin.quizzes.questions.index', $quiz) }}" class="admin-btn-secondary">{{ __('Manage questions') }}</a>
                <a href="{{ route('admin.quizzes.index') }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
