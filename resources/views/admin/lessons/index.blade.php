@extends('layouts.admin')

@section('title', 'Lessons')
@section('header', 'Lessons: ' . $chapter->title)

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('admin.courses.chapters.index', $chapter->course) }}" class="text-amber-600 hover:underline">← Back to chapters</a>
        <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add lesson</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($chapter->lessons as $lesson)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $lesson->title }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ ucfirst($lesson->type) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.lessons.edit', $lesson) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.lessons.destroy', $lesson) }}" method="post" class="inline" onsubmit="return confirm('Delete this lesson?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No lessons yet. <a href="{{ route('admin.chapters.lessons.create', $chapter) }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
