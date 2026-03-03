@extends('layouts.admin')

@section('title', 'Add Course')
@section('header', 'Add Course')

@section('content')
    <div class="max-w-2xl">
        <form action="{{ route('admin.courses.store') }}" method="post" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" id="category_id" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Select category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="instructor_id" class="block text-sm font-medium text-gray-700">Instructor</label>
                <select name="instructor_id" id="instructor_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Select instructor (optional)</option>
                    @foreach($instructors as $inst)
                        <option value="{{ $inst->id }}" {{ old('instructor_id') == $inst->id ? 'selected' : '' }}>{{ $inst->name }}</option>
                    @endforeach
                </select>
                @error('instructor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-2">Course type</span>
                <div class="flex gap-6">
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_free" value="1" {{ old('is_free', '1') == '1' ? 'checked' : '' }} class="rounded-full border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="ml-2">Free course (price = 0)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" name="is_free" value="0" {{ old('is_free') === '0' ? 'checked' : '' }} class="rounded-full border-gray-300 text-amber-600 focus:ring-amber-500">
                        <span class="ml-2">Paid course</span>
                    </label>
                </div>
            </div>
            <div id="price-field" class="{{ old('is_free', '1') == '1' ? 'hidden' : '' }}">
                <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                <input type="number" name="price" id="price" value="{{ old('price', 0) }}" min="0" step="0.01" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 max-w-xs">
                @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                <label for="banner" class="block text-sm font-medium text-gray-700">Course banner</label>
                <p class="mt-0.5 text-xs text-gray-500 mb-2">Upload an image that will appear as the course cover on the site. JPEG, PNG, GIF or WebP. Max 2MB.</p>
                <input type="file" name="banner" id="banner" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
                @error('banner')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700">Duration</label>
                    <input type="text" name="duration" id="duration" value="{{ old('duration') }}" placeholder="e.g. 8 hours" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @error('duration')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level</label>
                    <select name="level" id="level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="beginner" {{ old('level', 'beginner') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Create course</button>
                <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    </div>
    <script>
        document.querySelectorAll('input[name="is_free"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                document.getElementById('price-field').classList.toggle('hidden', this.value === '1');
            });
        });
    </script>
@endsection
