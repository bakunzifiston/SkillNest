@extends('layouts.admin')

@section('title', 'Create Quiz')
@section('header', 'Create Quiz')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.quizzes.store') }}" method="post" class="space-y-5">
            @csrf
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700">Course</label>
                <select name="course_id" id="course_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Select course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" {{ old('course_id', $selectedCourseId ?? '') == $c->id ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
                @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description (optional)</label>
                <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="passing_grade" class="block text-sm font-medium text-gray-700">Passing grade (%)</label>
                <input type="number" name="passing_grade" id="passing_grade" value="{{ old('passing_grade', 70) }}" min="0" max="100" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('passing_grade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700">Time limit (minutes, optional)</label>
                <input type="number" name="time_limit_minutes" id="time_limit_minutes" value="{{ old('time_limit_minutes') }}" min="1" max="300" placeholder="No limit" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('time_limit_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_published" value="1" {{ old('is_published', true) ? 'checked' : '' }} class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                    <span class="text-sm text-gray-700">Published (visible to students)</span>
                </label>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Create quiz</button>
                <a href="{{ route('admin.quizzes.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
