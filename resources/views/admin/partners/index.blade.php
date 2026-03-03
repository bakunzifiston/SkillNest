@extends('layouts.admin')

@section('title', 'Partner logos')
@section('header', 'Partner logos')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.partners.create') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg font-medium hover:bg-amber-600">Add partner logo</a>
    </div>
    <p class="text-gray-500 text-sm mb-4">These logos appear in the "Trusted by teams everywhere" section on the home page.</p>
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Logo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name (optional)</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($partners as $partner)
                <tr>
                    <td class="px-6 py-4">
                        <img src="{{ $partner->logo_url }}" alt="" class="object-contain rounded" style="height: 48px; max-width: 200px;">
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $partner->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <a href="{{ route('admin.partners.edit', $partner) }}" class="text-amber-600 hover:underline mr-2">Edit</a>
                        <form action="{{ route('admin.partners.destroy', $partner) }}" method="post" class="inline" onsubmit="return confirm('Remove this partner?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No partners yet. <a href="{{ route('admin.partners.create') }}" class="text-amber-600 hover:underline">Add one</a>.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
