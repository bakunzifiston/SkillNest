@extends('layouts.admin')

@section('title', 'Categories')
@section('header', 'Course Categories')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">Add and edit course categories.</p>
        <a href="{{ route('admin.categories.create') }}" class="admin-btn-accent">Add category</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Icon</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $category->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->slug }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $category->icon ?: '—' }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $category->courses_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="post" onsubmit="return confirm('Delete this category? Courses in it will also be deleted.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No categories yet.</p>
                                <a href="{{ route('admin.categories.create') }}" class="admin-btn-accent">Add category</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
