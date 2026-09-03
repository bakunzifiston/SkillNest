@extends('layouts.admin')

@section('title', 'Bundles')
@section('header', 'Bundles')

@section('content')
    <div class="mb-5 flex justify-end">
        <a href="{{ route('admin.bundles.create') }}" class="admin-btn-accent">Add bundle</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Slug</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Courses</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bundles as $bundle)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-navy">{{ $bundle->title }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $bundle->slug }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full
                                    @if($bundle->status === 'published') bg-success-light text-success-darker
                                    @elseif($bundle->status === 'archived') bg-slate-100 text-slate-600
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ $bundle->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 tabular-nums text-slate-600">{{ $bundle->courses_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.bundles.edit', $bundle) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.bundles.destroy', $bundle) }}" method="post" onsubmit="return confirm('Delete this bundle?');">
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
                                <p class="text-sm text-slate-500 mb-3">No bundles yet.</p>
                                <a href="{{ route('admin.bundles.create') }}" class="admin-btn-accent">Add bundle</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bundles->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $bundles->links() }}</div>
        @endif
    </div>
@endsection
