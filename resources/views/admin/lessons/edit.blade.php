@extends('layouts.admin')

@section('title', 'Edit Lesson')
@section('header', 'Edit lesson')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.courses.edit', $lesson->chapter->course) }}?tab=curriculum" class="text-amber-600 hover:underline">← Back to curriculum</a>
    </div>
    <div class="max-w-2xl">
        <form action="{{ route('admin.lessons.update', $lesson) }}" method="post" enctype="multipart/form-data" class="space-y-5" id="lesson-form">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Lesson title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $lesson->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Content type</label>
                <select name="type" id="type" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @foreach(\App\Models\Lesson::types() as $value => $label)
                        <option value="{{ $value }}" {{ old('type', $lesson->type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div id="field-text" class="lesson-type-fields {{ $lesson->type === 'text' ? '' : 'hidden' }}">
                <label for="content" class="block text-sm font-medium text-gray-700">Text content</label>
                <textarea name="content" id="content" rows="6" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('content', $lesson->content) }}</textarea>
                @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-youtube" class="lesson-type-fields {{ $lesson->type === 'youtube' ? '' : 'hidden' }}">
                <label for="source_url" class="block text-sm font-medium text-gray-700">YouTube (or video) URL</label>
                <input type="url" name="source_url" id="source_url" value="{{ old('source_url', $lesson->source_url) }}" placeholder="https://www.youtube.com/watch?v=..." class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('source_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-pdf" class="lesson-type-fields {{ $lesson->type === 'pdf' ? '' : 'hidden' }}">
                <label for="pdf_file" class="block text-sm font-medium text-gray-700">Upload PDF</label>
                @if($lesson->file_path)
                    <p class="mt-1 text-sm text-gray-500 mb-1">Current file: {{ basename($lesson->file_path) }}. Upload a new file to replace.</p>
                @endif
                <input type="file" name="pdf_file" id="pdf_file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-700">
                <p class="mt-1 text-xs text-gray-500">Max 50MB. Leave empty to keep current.</p>
                @error('pdf_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div id="field-video" class="lesson-type-fields {{ $lesson->type === 'video' ? '' : 'hidden' }}">
                <label for="video_file" class="block text-sm font-medium text-gray-700">Upload video</label>
                @if($lesson->file_path)
                    <p class="mt-1 text-sm text-gray-500 mb-1">Current file: {{ basename($lesson->file_path) }}. Upload a new file to replace.</p>
                @endif
                <input type="file" name="video_file" id="video_file" accept="video/mp4,video/quicktime,video/x-msvideo,video/webm" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-50 file:text-amber-700">
                <p class="mt-1 text-xs text-gray-500">MP4, MOV, AVI or WebM. Max 100MB.</p>
                @error('video_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Update lesson</button>
                <a href="{{ route('admin.courses.edit', $lesson->chapter->course) }}?tab=curriculum" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        function toggleLessonFields() {
            var type = document.getElementById('type').value;
            document.querySelectorAll('.lesson-type-fields').forEach(function(el) { el.classList.add('hidden'); });
            var field = document.getElementById('field-' + type);
            if (field) field.classList.remove('hidden');
        }
        document.getElementById('type').addEventListener('change', toggleLessonFields);
        toggleLessonFields();
    </script>
@endsection
