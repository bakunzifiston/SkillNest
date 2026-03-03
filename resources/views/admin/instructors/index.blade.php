@extends('layouts.admin')

@section('title', 'Instructors')
@section('header', 'Instructors')

@section('content')
    <div class="mb-6 flex justify-between items-center">
        <p class="text-gray-600">Manage instructors. Add instructors before creating courses so you can assign them.</p>
        <a href="{{ route('admin.instructors.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add instructor</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($instructors as $instructor)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $instructor->name }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $instructor->email ?? '—' }}</td>
                    <td class="px-6 py-4 text-gray-500">{{ $instructor->courses_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.instructors.edit', $instructor) }}" class="text-amber-600 hover:underline mr-3">Edit</a>
                        <form action="{{ route('admin.instructors.destroy', $instructor) }}" method="post" class="inline" onsubmit="return confirm('Delete this instructor? Their courses will be unassigned.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">No instructors yet. <a href="{{ route('admin.instructors.create') }}" class="text-amber-600 hover:underline">Add one</a> to assign to courses.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($instructors->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $instructors->links() }}</div>
        @endif
    </div>
@endsection
