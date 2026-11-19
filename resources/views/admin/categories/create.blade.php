@extends('layouts.admin')

@section('title', __('Add Category'))
@section('header', __('Add Category'))

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <form action="{{ route('admin.categories.store') }}" method="post" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-navy">{{ __('Name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="icon" class="block text-sm font-medium text-navy">{{ __('Icon') }}</label>
                    <input type="text" name="icon" id="icon" value="{{ old('icon') }}" placeholder="{{ __('e.g. 💻 or dev') }}" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
                    <p class="mt-1 text-xs text-slate-400">{{ __('Optional emoji or short label.') }}</p>
                    @error('icon')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @include('admin.partials.translation-fields', [
                    'model' => null,
                    'fields' => [
                        'name' => ['label' => __('Name'), 'type' => 'input'],
                    ],
                ])
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="admin-btn-accent">{{ __('Create category') }}</button>
                    <a href="{{ route('admin.categories.index') }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
