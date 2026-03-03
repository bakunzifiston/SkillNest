@extends('layouts.admin')

@section('title', 'Courses')
@section('header', 'Courses')

@section('content')
    <div class="mb-6 flex flex-wrap gap-4 justify-between items-center">
        <div class="flex gap-2 items-center">
            <form action="{{ route('admin.courses.index') }}" method="get" class="flex gap-2">
                <select name="category_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add course</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($courses as $course)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ Str::limit($course->title, 40) }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $course->category->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $course->price > 0 ? '$' . number_format($course->price, 0) : 'Free' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $course->duration ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.courses.edit', $course) }}" class="text-amber-600 hover:underline mr-2">Edit</a>
                        <a href="{{ route('admin.courses.edit', $course) }}?tab=curriculum" class="text-gray-600 hover:underline mr-2">Curriculum</a>
                        <form action="{{ route('admin.courses.destroy', $course) }}" method="post" class="inline" onsubmit="return confirm('Delete this course?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No courses yet. <a href="{{ route('admin.courses.create') }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($courses->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $courses->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
