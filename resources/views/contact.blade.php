@extends('layouts.site')

@section('title', 'Contact')

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0">
            <img
                src="{{ asset('images/banner/agents-field.jpg') }}"
                alt=""
                class="w-full h-full object-cover object-center opacity-40"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-navy via-navy/90 to-navy/70"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 lg:py-20">
            <nav class="text-sm text-slate-300 mb-4" aria-label="Breadcrumb">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                    <li aria-hidden="true">/</li>
                    <li class="text-white font-medium">Contact</li>
                </ol>
            </nav>
            <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.18em] text-accent">We’re here to help</p>
            <h1 class="mt-4 font-display font-bold text-4xl sm:text-5xl tracking-tight max-w-3xl">
                Contact us
            </h1>
            <p class="mt-5 max-w-2xl text-lg text-slate-200 leading-relaxed">
                Questions about the Academy, your courses, or the Digital Jobs for Youth in Health program? Send a message and we’ll get back to you.
            </p>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">
                <div class="lg:col-span-7">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Send a message</h2>
                    <p class="mt-2 text-slate-600">Fill in the form and we’ll reply as soon as we can.</p>

                    @if(session('success'))
                        <div class="mt-6 px-4 py-3 rounded-xl bg-success-light text-success-darker border border-success-muted" role="status">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="post" class="mt-8 space-y-5">
                        @csrf
                        <div class="grid sm:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name') }}"
                                    required
                                    autocomplete="name"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-primary focus:border-primary"
                                    placeholder="Your name"
                                >
                                @error('name')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-primary focus:border-primary"
                                    placeholder="you@example.com"
                                >
                                @error('email')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700 mb-1.5">Subject <span class="text-slate-400 font-normal">(optional)</span></label>
                            <input
                                type="text"
                                name="subject"
                                id="subject"
                                value="{{ old('subject') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="What is this about?"
                            >
                            @error('subject')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-1.5">Message</label>
                            <textarea
                                name="message"
                                id="message"
                                rows="6"
                                required
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-primary focus:border-primary"
                                placeholder="How can we help?"
                            >{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button
                            type="submit"
                            class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition shadow-brand-accent focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
                        >
                            Send message
                        </button>
                    </form>
                </div>

                <aside class="lg:col-span-5 space-y-6">
                    <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6 sm:p-8">
                        <h2 class="font-display font-bold text-xl text-navy">Other ways to reach us</h2>
                        <ul class="mt-6 space-y-5">
                            <li class="flex gap-4">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Email</p>
                                    <a href="mailto:bakunzifiston@gmail.com" class="mt-1 block text-navy font-medium hover:text-primary break-all transition">bakunzifiston@gmail.com</a>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Phone</p>
                                    <a href="tel:0783092757" class="mt-1 block text-navy font-medium hover:text-primary transition">0783092757</a>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-light text-primary">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Address</p>
                                    <p class="mt-1 text-navy font-medium">Kigali, Gasabo</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="relative overflow-hidden rounded-2xl aspect-[4/3] ring-1 ring-slate-200 shadow-brand">
                        <img
                            src="{{ asset('images/banner/agents-workshop.jpg') }}"
                            alt="KoraLink agents in a training workshop"
                            class="absolute inset-0 w-full h-full object-cover object-center"
                            loading="lazy"
                        >
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
