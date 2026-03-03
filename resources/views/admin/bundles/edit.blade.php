@extends('layouts.admin')

@section('title', 'Edit Bundle')
@section('header', 'Edit: ' . $bundle->title)

@section('content')
    @if(session('success'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-4 px-4 py-2 bg-amber-100 text-amber-800 rounded-lg">{{ session('info') }}</div>
    @endif

    <div class="max-w-2xl mb-10">
        <form action="{{ route('admin.bundles.update', $bundle) }}" method="post" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" value="{{ old('title', $bundle->title) }}" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $bundle->slug) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">{{ old('description', $bundle->description) }}</textarea>
            </div>
            <div class="p-4 bg-amber-50/50 rounded-xl border border-amber-100">
                <label for="thumbnail" class="block text-sm font-medium text-gray-700">Thumbnail</label>
                @if($bundle->thumbnail_url)
                    <div class="mt-2 mb-3">
                        <img src="{{ $bundle->thumbnail_url }}" alt="" class="rounded-lg border border-gray-200 max-h-32 object-cover">
                        <p class="mt-1 text-xs text-gray-500">Upload new to replace.</p>
                    </div>
                @endif
                <input type="file" name="thumbnail" id="thumbnail" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-amber-500 file:text-white file:font-medium hover:file:bg-amber-600">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="draft" {{ old('status', $bundle->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="published" {{ old('status', $bundle->status) === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="archived" {{ old('status', $bundle->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Save bundle</button>
                <a href="{{ route('admin.bundles.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Back to bundles</a>
            </div>
        </form>
    </div>

    <h2 class="text-lg font-semibold text-gray-800 mb-3">Courses in this bundle (learning sequence)</h2>
    @if($bundle->courses->isEmpty())
        <p class="text-gray-500 mb-4">No courses yet. Add one below.</p>
    @else
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remove</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($bundle->courses as $index => $course)
                    <tr>
                        <td class="px-6 py-4 text-gray-600">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $course->title }}</td>
                        <td class="px-6 py-4 text-right">
                            <form action="{{ route('admin.bundles.courses.remove', [$bundle, $course]) }}" method="post" class="inline" onsubmit="return confirm('Remove this course from the bundle?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <form action="{{ route('admin.bundles.courses.order', $bundle) }}" method="post" class="hidden" id="order-form">
            @csrf
            @method('PUT')
            @foreach($bundle->courses as $course)
                <input type="hidden" name="order[]" value="{{ $course->id }}">
            @endforeach
        </form>
    @endif

    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 max-w-md">
        <h3 class="text-sm font-medium text-gray-700 mb-2">Add course to bundle</h3>
        <form action="{{ route('admin.bundles.courses.add', $bundle) }}" method="post" class="flex gap-2">
            @csrf
            <select name="course_id" required class="flex-1 rounded-lg border-gray-300 text-sm">
                <option value="">Select course</option>
                @foreach($coursesNotInBundle as $c)
                    <option value="{{ $c->id }}">{{ $c->title }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-amber-500 text-white rounded-lg text-sm font-medium hover:bg-amber-600">Add</button>
        </form>
        @if($coursesNotInBundle->isEmpty())
            <p class="mt-2 text-sm text-gray-500">All courses are already in this bundle.</p>
        @endif
    </div>
@endsection
