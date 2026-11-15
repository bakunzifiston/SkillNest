@extends('layouts.admin')

@section('title', 'Edit Partner Organization')
@section('header', 'Edit Partner Organization')

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <form action="{{ route('admin.partner-organizations.update', $organization) }}" method="post" class="space-y-5">
                @csrf
                @method('PUT')
                @include('admin.partner-organizations._form', ['organization' => $organization])
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="admin-btn-accent">Save changes</button>
                    <a href="{{ route('admin.partner-organizations.index') }}" class="admin-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
