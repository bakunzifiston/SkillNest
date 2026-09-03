@extends('layouts.admin')

@section('title', 'Courses')
@section('header', 'Courses')

@section('content')
    <div class="mb-5 flex flex-wrap gap-3 justify-between items-center">
        <form action="{{ route('admin.courses.index') }}" method="get">
            <select name="category_id" onchange="this.form.submit()" class="rounded-xl border-slate-300 text-sm">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('admin.courses.create') }}" class="admin-btn-accent">Add course</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Category</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Price</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Duration</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($courses as $course)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ Str::limit($course->title, 40) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $course->category->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $course->price > 0 ? '$' . number_format($course->price, 0) : 'Free' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $course->duration ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="admin-btn-secondary">Edit</a>
                                    <a href="{{ route('admin.courses.edit', $course) }}?tab=curriculum" class="admin-btn-secondary">Curriculum</a>
                                    <form action="{{ route('admin.courses.destroy', $course) }}" method="post" onsubmit="return confirm('Delete this course?');">
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
                                <p class="text-sm text-slate-500 mb-3">No courses yet.</p>
                                <a href="{{ route('admin.courses.create') }}" class="admin-btn-accent">Add course</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($courses->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $courses->withQueryString()->links() }}</div>
        @endif
    </div>
@endsection
