@extends('layouts.admin')

@section('title', 'Edit partner')
@section('header', 'Edit partner')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.partners.update', $partner) }}" method="post" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name (optional)</label>
                <input type="text" name="name" id="name" value="{{ old('name', $partner->name) }}" placeholder="e.g. Acme Inc" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
            </div>
            <div class="p-4 bg-accent-light/50 rounded-xl border border-accent-muted">
                <label for="logo" class="block text-sm font-medium text-gray-700">Logo image</label>
                @if($partner->logo_url)
                    <div class="mt-2 mb-3">
                        <p class="text-xs text-gray-500 mb-1">Current:</p>
                        <img src="{{ $partner->logo_url }}" alt="" class="object-contain border border-gray-200 rounded p-2" style="height: 56px; max-width: 260px;">
                        <p class="mt-1 text-xs text-gray-500">Upload new to replace.</p>
                    </div>
                @endif
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-accent file:text-white file:font-medium hover:file:bg-accent-dark">
                @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">Save</button>
                <a href="{{ route('admin.partners.index') }}" class="admin-btn-secondary">Back to partners</a>
            </div>
        </form>
    </div>
@endsection
