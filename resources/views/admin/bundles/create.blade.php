@extends('layouts.admin')

@section('title', 'Add Bundle')
@section('header', 'Add Bundle')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.bundles.store') }}" method="post" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug (optional)</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="full-web-developer-path" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <p class="mt-0.5 text-xs text-gray-500">Leave blank to auto-generate from title.</p>
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail (cover image)</label>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
                <p class="mt-0.5 text-xs text-gray-500">JPEG, PNG, GIF or WebP. Max 2MB.</p>
                @error('thumbnail')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Create bundle</button>
                <a href="{{ route('admin.bundles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
