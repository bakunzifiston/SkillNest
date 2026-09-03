@extends('layouts.admin')

@section('title', 'Lessons')
@section('header', 'Lessons: ' . $chapter->title)

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <a href="{{ route('admin.courses.chapters.index', $chapter->course) }}" class="admin-btn-secondary">Back to chapters</a>
        <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="admin-btn-accent">Add lesson</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($chapter->lessons as $lesson)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $lesson->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ ucfirst($lesson->type) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.lessons.edit', $lesson) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="post" onsubmit="return confirm('Delete this lesson?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No lessons yet.</p>
                                <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="admin-btn-accent">Add lesson</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
