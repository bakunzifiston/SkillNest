@extends('layouts.admin')

@section('title', 'Add partner logo')
@section('header', 'Add partner logo')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.partners.store') }}" method="post" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name (optional)</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="e.g. Acme Inc" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                <p class="mt-0.5 text-xs text-gray-500">Used as alt text if logo image is used.</p>
            </div>
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                <label for="logo" class="block text-sm font-medium text-gray-700">Logo image *</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
                <p class="mt-0.5 text-xs text-gray-500">JPEG, PNG, GIF, WebP or SVG. Max 2MB. Recommended: transparent background, max height ~48px.</p>
                @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add partner</button>
                <a href="{{ route('admin.partners.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
