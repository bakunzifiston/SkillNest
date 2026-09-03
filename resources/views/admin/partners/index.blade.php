@extends('layouts.admin')

@section('title', 'Partner logos')
@section('header', 'Partner logos')

@section('content')
    <div class="mb-5 flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-500">These logos appear in the partners section on the home page.</p>
        <a href="{{ route('admin.partners.create') }}" class="admin-btn-accent">Add partner logo</a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Logo</th>
                        <th class="px-4 py-2.5 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                        <th class="px-4 py-2.5 text-right text-xs font-medium uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($partners as $partner)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3">
                                <img src="{{ $partner->logo_url }}" alt="" class="object-contain rounded h-12 max-w-[12rem]">
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $partner->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.partners.edit', $partner) }}" class="admin-btn-secondary">Edit</a>
                                    <form action="{{ route('admin.partners.destroy', $partner) }}" method="post" onsubmit="return confirm('Remove this partner?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="admin-btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500 mb-3">No partners yet.</p>
                                <a href="{{ route('admin.partners.create') }}" class="admin-btn-accent">Add partner logo</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
