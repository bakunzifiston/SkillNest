@extends('layouts.admin')

@section('title', 'Bundles')
@section('header', 'Bundles')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.bundles.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add bundle</a>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Courses</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($bundles as $bundle)
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $bundle->title }}</td>
                    <td class="px-6 py-4 text-gray-500 text-sm">{{ $bundle->slug }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-medium rounded-full
                            @if($bundle->status === 'published') bg-green-100 text-green-800
                            @elseif($bundle->status === 'archived') bg-gray-100 text-gray-600
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ $bundle->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $bundle->courses_count }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.bundles.edit', $bundle) }}" class="text-amber-600 hover:underline mr-2">Edit</a>
                        <form action="{{ route('admin.bundles.destroy', $bundle) }}" method="post" class="inline" onsubmit="return confirm('Delete this bundle?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No bundles yet. <a href="{{ route('admin.bundles.create') }}" class="text-amber-600 hover:underline">Create one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($bundles->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">{{ $bundles->links() }}</div>
        @endif
    </div>
@endsection
