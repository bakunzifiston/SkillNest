@extends('layouts.admin')

@section('title', 'Site settings')
@section('header', 'Site settings')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.settings.update') }}" method="post" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div class="p-6 bg-white rounded-xl border border-gray-200">
                <h3 class="font-semibold text-gray-800 mb-2">Platform logo</h3>
                <p class="text-sm text-gray-500 mb-4">Upload a logo to display in the header and footer instead of the site name. Recommended: transparent PNG or SVG, max height ~40px for header.</p>
                @if($logoUrl)
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-2">Current logo:</p>
                        <img src="{{ $logoUrl }}" alt="Site logo" class="object-contain object-left border border-gray-200 rounded p-2 bg-gray-50" style="height: 56px; max-width: 320px;">
                        <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            Remove logo (use site name again)
                        </label>
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
                <p class="mt-1 text-xs text-gray-500">JPEG, PNG, GIF, WebP or SVG. Max 2MB.</p>
                @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Save settings</button>
        </form>
    </div>
@endsection
