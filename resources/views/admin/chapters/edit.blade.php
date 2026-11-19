@extends('layouts.admin')

@section('title', __('Edit Chapter'))
@section('header', __('Edit section'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.courses.edit', $chapter->course) }}?tab=curriculum" class="admin-btn-secondary">{{ __('Back to curriculum') }}</a>
    </div>
    <div class="max-w-xl">
        <form action="{{ route('admin.chapters.update', $chapter) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">{{ __('Chapter title') }}</label>
                <input type="text" name="title" id="title" value="{{ old('title', $chapter->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            @include('admin.partials.translation-fields', [
                'model' => $chapter,
                'fields' => [
                    'title' => ['label' => __('Chapter title'), 'type' => 'input'],
                ],
            ])
            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">{{ __('Update section') }}</button>
                <a href="{{ route('admin.courses.edit', $chapter->course) }}?tab=curriculum" class="admin-btn-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
