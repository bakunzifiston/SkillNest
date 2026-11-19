@extends('layouts.admin')

@section('title', __('Edit Role'))
@section('header', $role->isSuperAdmin() ? __('Super Admin') : __('Edit role'))

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.roles.index') }}" class="text-sm text-slate-500 hover:text-primary inline-flex items-center gap-1">
            <span aria-hidden="true">←</span> {{ __('All roles') }}
        </a>
    </div>

    <form action="{{ route('admin.roles.update', $role) }}" method="post" class="max-w-4xl space-y-5 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
        @csrf
        @method('PUT')
        @include('admin.roles._form', ['role' => $role])
        <div class="flex gap-3 pt-2">
            <button type="submit" class="admin-btn-accent">{{ $locked ? __('Save name') : __('Save role') }}</button>
            <a href="{{ route('admin.roles.index') }}" class="admin-btn-secondary">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection
