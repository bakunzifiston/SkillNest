@extends('layouts.site')

@section('title', 'Profile')

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0 opacity-[0.08]" aria-hidden="true" style="background-image: radial-gradient(circle at 15% 20%, #F16029 0, transparent 38%), radial-gradient(circle at 85% 10%, #19499B 0, transparent 32%);"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">Profile</li>
                </ol>
            </nav>
            <h1 class="font-display font-bold text-3xl sm:text-4xl tracking-tight">Profile</h1>
            <p class="mt-3 max-w-2xl text-slate-300 leading-relaxed">
                Manage your account details, password, and security settings.
            </p>
            <p class="mt-5 text-sm text-slate-400 truncate max-w-xl">
                {{ trim(($user->displayFirstName() ?: '').' '.($user->displayLastName() ?: '')) ?: $user->email }}
                <span class="text-slate-500">·</span>
                {{ $user->email }}
            </p>
        </div>
    </section>

    <section class="py-12 lg:py-16 bg-slate-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                @include('profile.partials.update-password-form')
            </div>

            <div class="rounded-2xl border border-red-100 bg-white p-6 sm:p-8">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </section>
@endsection
