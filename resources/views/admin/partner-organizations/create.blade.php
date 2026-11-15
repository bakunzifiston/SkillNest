@extends('layouts.admin')

@section('title', 'Add Partner Organization')
@section('header', 'Add Partner Organization')

@section('content')
    <div class="max-w-xl">
        <div class="bg-white rounded-xl border border-slate-200 p-5 sm:p-6">
            <form action="{{ route('admin.partner-organizations.store') }}" method="post" class="space-y-5">
                @csrf
                @include('admin.partner-organizations._form', ['organization' => null])
                <div class="flex flex-wrap gap-2 pt-1">
                    <button type="submit" class="admin-btn-accent">Add organization</button>
                    <a href="{{ route('admin.partner-organizations.index') }}" class="admin-btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
