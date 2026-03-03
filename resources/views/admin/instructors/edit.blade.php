@extends('layouts.admin')

@section('title', 'Edit Instructor')
@section('header', 'Edit Instructor')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.instructors.update', $instructor) }}" method="post" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $instructor->name) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email (optional)</label>
                <input type="email" name="email" id="email" value="{{ old('email', $instructor->email) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700">Bio (optional)</label>
                <textarea name="bio" id="bio" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('bio', $instructor->bio) }}</textarea>
                @error('bio')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Update instructor</button>
                <a href="{{ route('admin.instructors.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
