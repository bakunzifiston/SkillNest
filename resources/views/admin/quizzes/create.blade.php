@extends('layouts.admin')

@section('title', __('Create Quiz'))
@section('header', __('Create Quiz'))

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.quizzes.store') }}" method="post" class="space-y-5">
            @csrf
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">{{ __('Course') }}</label>
                <select name="course_id" id="course_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    <option value="">{{ __('Select course') }}</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id', $selectedCourseId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
                @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @include('admin.quizzes.partials.placement', ['courseLocked' => false, 'selectedChapterId' => request('chapter_id'), 'selectedLessonId' => null])
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description (optional)') }}</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="passing_grade" class="block text-sm font-medium text-gray-700">{{ __('Passing grade (%)') }}</label>
                <input type="number" name="passing_grade" id="passing_grade" value="{{ old('passing_grade', 70) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('passing_grade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700">{{ __('Time limit (minutes, optional)') }}</label>
                <input type="number" name="time_limit_minutes" id="time_limit_minutes" value="{{ old('time_limit_minutes') }}" min="1" max="300" placeholder="{{ __('No limit') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('time_limit_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @include('admin.quizzes.partials.attempt-rules', ['quiz' => null])
            <div>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" {{ (bool) old('is_published', true) ? 'checked' : '' }} class="rounded border-gray-300 text-primary focus:ring-accent">
                    <span class="text-sm text-gray-700">{{ __('Published (visible to students)') }}</span>
                </label>
            </div>
            @include('admin.partials.translation-fields', [
                'model' => null,
                'fields' => [
                    'title' => ['label' => __('Title'), 'type' => 'input'],
                    'description' => ['label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            @include('admin.partials.translation-fields', [
                'model' => null,
                'fields' => [
                    'title' => ['label' => __('Title'), 'type' => 'input'],
                    'description' => ['label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                ],
            ])

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">{{ __('Create quiz') }}</button>
                <a href="{{ route('admin.quizzes.index') }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
