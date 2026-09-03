@extends('layouts.admin')

@section('title', 'Instructors')
@section('header', 'Instructors')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">Add instructors before creating courses so you can assign them.</p>
        <a href="{{ route('admin.instructors.create') }}" class="admin-btn-accent">Add instructor</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Email</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($instructors as $instructor)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $instructor->name }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $instructor->email ?? '—' }}</td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $instructor->courses_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.instructors.edit', $instructor) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="post" onsubmit="return confirm('Delete this instructor? Their courses will be unassigned.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No instructors yet.</p>
                                <a href="{{ route('admin.instructors.create') }}" class="admin-btn-accent">Add instructor</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($instructors->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $instructors->links() }}</div>
        @endif
    </div>
@endsection
