@extends('layouts.admin')

@section('title', 'Chapters')
@section('header', 'Chapters: ' . $course->title)

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('admin.courses.edit', $course) }}" class="admin-btn-secondary">Back to course</a>
        <a href="{{ route('admin.courses.chapters.create', $course) }}" class="admin-btn-accent">Add chapter</a>
    </div>
    <div class="space-y-3">
        @forelse($course->chapters as $chapter)
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-navy">{{ $chapter->title }}</h3>
                    <p class="text-sm text-slate-500">{{ $chapter->lessons->count() }} lesson(s)</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.chapters.lessons.index', $chapter) }}" class="admin-btn-secondary">Lessons</a>
                    <a href="{{ route('admin.chapters.edit', $chapter) }}" class="admin-btn-secondary">Edit</a>
                    <form action="{{ route('admin.chapters.destroy', $chapter) }}" method="post" onsubmit="return confirm('Delete this chapter and all its lessons?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 px-4 py-10 text-center">
                <p class="text-sm text-slate-500 mb-3">No chapters yet.</p>
                <a href="{{ route('admin.courses.chapters.create', $course) }}" class="admin-btn-accent">Add chapter</a>
            </div>
        @endforelse
    </div>
@endsection
