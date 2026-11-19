@extends('layouts.admin')

@section('title', __('Chapters'))
@section('header', __('Chapters: :title', ['title' => $course->title]))

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('admin.courses.edit', $course) }}" class="admin-btn-secondary">{{ __('Back to course') }}</a>
        <a href="{{ route('admin.courses.chapters.create', $course) }}" class="admin-btn-accent">{{ __('Add chapter') }}</a>
    </div>
    <div class="space-y-3">
        @forelse($course->chapters as $chapter)
            <div class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-navy">{{ $chapter->title }}</h3>
                    <p class="text-sm text-slate-500">{{ trans_choice('{1} :count lesson|[2,*] :count lessons', $chapter->lessons->count()) }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.chapters.lessons.index', $chapter) }}" class="admin-btn-secondary">{{ __('Lessons') }}</a>
                    <a href="{{ route('admin.chapters.edit', $chapter) }}" class="admin-btn-secondary">{{ __('Edit') }}</a>
                    <form action="{{ route('admin.chapters.destroy', $chapter) }}" method="post" onsubmit="return confirm({{ Js::from(__('Delete this chapter and all its lessons?')) }});">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="admin-btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 px-4 py-10 text-center">
                <p class="text-sm text-slate-500 mb-3">{{ __('No chapters yet.') }}</p>
                <a href="{{ route('admin.courses.chapters.create', $course) }}" class="admin-btn-accent">{{ __('Add chapter') }}</a>
            </div>
        @endforelse
    </div>
@endsection
