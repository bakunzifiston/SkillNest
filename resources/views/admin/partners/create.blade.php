@extends('layouts.admin')

@section('title', 'Add partner logo')
@section('header', 'Add partner logo')

@section('content')
    <div class="max-w-xl">
        <form action="{{ route('admin.partners.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100">
                    <h2 class="font-display font-semibold text-sm text-navy">Partner details</h2>
                    <p class="text-[11px] text-slate-400 mt-0.5">Logos appear in the partners section on the home page.</p>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-navy mb-1.5">Name <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            value="{{ old('name') }}"
                            placeholder="e.g. Acme Inc"
                            class="block w-full rounded-xl border-slate-300 text-sm focus:border-primary focus:ring-primary"
                        >
                        <p class="mt-1 text-xs text-slate-400">Used as alt text for the logo.</p>
                    </div>
                    <div>
                        <label for="logo" class="block text-sm font-medium text-navy mb-1.5">Logo image *</label>
                        <input
                            type="file"
                            name="logo"
                            id="logo"
                            accept="image/jpeg,image/png,image/jpg,image/gif,image/webp,image/svg+xml"
                            required
                            class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-accent file:text-white file:font-medium hover:file:bg-accent-dark"
                        >
                        <p class="mt-1.5 text-xs text-slate-400">JPEG, PNG, GIF, WebP or SVG. Max 2MB.</p>
                        @error('logo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="admin-btn-accent">Add partner</button>
                <a href="{{ route('admin.partners.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
