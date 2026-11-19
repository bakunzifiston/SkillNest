@extends('layouts.admin')

@section('title', __('Lessons'))
@section('header', __('Lessons: :title', ['title' => $chapter->title]))

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('admin.courses.chapters.index', $chapter->course) }}" class="admin-btn-secondary">{{ __('Back to chapters') }}</a>
        <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="admin-btn-accent">{{ __('Add lesson') }}</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Title') }}</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">{{ __('Type') }}</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($chapter->lessons as $lesson)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $lesson->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ match ($lesson->type) { 'text' => __('Text'), 'youtube' => __('Youtube'), 'pdf' => __('Pdf'), 'video' => __('Video'), default => ucfirst($lesson->type) } }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="admin-btn-secondary">{{ __('Edit') }}</a>
                                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="post" onsubmit="return confirm({{ Js::from(__('Delete this lesson?')) }});">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">{{ __('No lessons yet.') }}</p>
                                <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="admin-btn-accent">{{ __('Add lesson') }}</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
