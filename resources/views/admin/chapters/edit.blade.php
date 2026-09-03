@extends('layouts.admin')

@section('title', 'Edit Chapter')
@section('header', 'Edit section')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.courses.edit', $chapter->course) }}?tab=curriculum" class="admin-btn-secondary">Back to curriculum</a>
    </div>
    <div class="max-w-xl">
        <form action="{{ route('admin.chapters.update', $chapter) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Chapter title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $chapter->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">Update section</button>
                <a href="{{ route('admin.courses.edit', $chapter->course) }}?tab=curriculum" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
