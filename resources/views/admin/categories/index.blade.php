@extends('layouts.admin')

@section('title', 'Categories')
@section('header', 'Course Categories')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">Add and edit course categories.</p>
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add category</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Icon</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($categories as $category)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $category->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $category->slug }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $category->icon ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $category->courses_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="post" class="inline" onsubmit="return confirm('Delete this category? Courses in it will also be deleted.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No categories yet. <a href="{{ route('admin.categories.create') }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($categories->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
