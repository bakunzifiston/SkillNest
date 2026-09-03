@extends('layouts.admin')

@section('title', 'Edit Category')
@section('header', 'Edit Category')

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <form action="{{ route('admin.categories.update', $category) }}" method="post" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-medium text-navy">Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $category->name) }}" required class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="icon" class="block text-sm font-medium text-navy">Icon</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon', $category->icon) }}" placeholder="e.g. 💻" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-400">Optional emoji or short label.</p>
                    @error('icon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="admin-btn-accent">Update category</button>
                    <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
