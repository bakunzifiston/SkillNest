@extends('layouts.admin')

@section('title', 'Edit User')
@section('header', 'Edit user')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.users.show', $user) }}" class="text-sm text-slate-500 hover:text-primary inline-flex items-center gap-1">
            <span aria-hidden="true">←</span> {{ trim(($user->displayFirstName() ?: '').' '.($user->displayLastName() ?: '')) ?: $user->email }}
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="post" class="max-w-4xl space-y-5 bg-white rounded-2xl border border-slate-200 p-5 sm:p-6">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user])
        <div class="flex gap-3 pt-2">
            <button type="submit" class="admin-btn-accent">Save user</button>
            <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">Cancel</a>
        </div>
    </form>
@endsection
