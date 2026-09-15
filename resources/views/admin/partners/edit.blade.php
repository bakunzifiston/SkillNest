@extends('layouts.admin')

@section('title', 'Edit partner')
@section('header', 'Edit partner')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.partners.update', $partner) }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="font-display font-semibold text-sm text-navy">Partner details</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-navy mb-1.5">Name <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name', $partner->name) }}"
                            placeholder="e.g. Acme Inc"
                            class="block w-full rounded-xl border-slate-300 text-sm focus:border-primary focus:ring-primary"
                        >
                    </div>
                    <div>
                        <label for="logo" class="block text-sm font-medium text-navy mb-1.5">Logo image</label>
                        @if($partner->logo_url)
                            <div class="mb-3 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <img src="{{ $partner->logo_url }}" alt="{{ $partner->name ?? 'Partner logo' }}" class="object-contain" style="height: 48px; max-width: 240px;">
                            </div>
                            <p class="mb-2 text-xs text-slate-400">Upload a new file to replace the current logo.</p>
                        @endif
                        <input
                            type="file"
                            name="logo"
                            id="logo"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-accent file:text-white file:font-medium hover:file:bg-accent-dark"
                        >
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">Save</button>
                <a href="{{ route('admin.partners.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
