@extends('layouts.admin')

@section('title', 'Chapters')
@section('header', 'Chapters: ' . $course->title)

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.courses.edit', $course) }}" class="text-amber-600 hover:underline">← Back to course</a>
        <a href="{{ route('admin.courses.chapters.create', $course) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add chapter</a>
    </div>
    <div class="space-y-4">
        @forelse($course->chapters as $chapter)
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $chapter->title }}</h3>
                <p class="text-sm text-gray-500">{{ $chapter->lessons->count() }} lesson(s)</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.chapters.lessons.index', $chapter) }}" class="text-amber-600 hover:underline">Lessons</a>
                <a href="{{ route('admin.chapters.edit', $chapter) }}" class="text-gray-600 hover:underline">Edit</a>
                <form action="{{ route('admin.chapters.destroy', $chapter) }}" method="post" class="inline" onsubmit="return confirm('Delete this chapter and all its lessons?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                </form>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
            No chapters yet. <a href="{{ route('admin.courses.chapters.create', $course) }}" class="text-amber-600 hover:underline">Add the first chapter</a>.
        </div>
        @endforelse
    </div>
@endsection
