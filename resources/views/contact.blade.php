@extends('layouts.site')

@section('title', 'Contact')

@section('content')
    <section class="relative text-white overflow-hidden">
        <div class="absolute inset-0">
            <img
                src="{{ asset('images/banner/agents-field.jpg') }}"
                alt="KoraLink Agents"
                class="w-full h-full object-cover object-center"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-navy/90 via-navy/70 to-navy/40"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 lg:py-20">
            <p class="text-sm font-semibold uppercase tracking-wider text-accent">KoraLink Agents Academy</p>
            <h1 class="mt-3 font-display font-bold text-4xl sm:text-5xl tracking-tight">Contact us</h1>
            <p class="mt-4 max-w-2xl text-lg text-slate-200 leading-relaxed">Have a question about the academy, your courses, or the Digital Jobs for Youth in Health program? Send us a message and we’ll get back to you.</p>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-8 lg:gap-12 items-start">
                <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
                    <h2 class="font-display font-bold text-2xl text-navy">Send a message</h2>
                    <p class="mt-2 text-slate-600">We’ll reply as soon as we can.</p>

                    @if(session('success'))
                        <div class="mt-6 px-4 py-3 rounded-xl bg-success-light text-success-darker border border-success-muted">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="post" class="mt-6 space-y-5">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Your name">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="you@example.com">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="What is this about?">
                            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                            <textarea name="message" id="message" rows="5" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="Your message">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition shadow-brand-accent">Send message</button>
                    </form>
                </div>

                <aside class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl bg-navy text-white p-6 sm:p-8">
                        <h2 class="font-display font-bold text-xl">Other ways to reach us</h2>
                        <ul class="mt-6 space-y-5 text-slate-200">
                            <li>
                                <p class="text-xs font-semibold uppercase tracking-wider text-accent">Email</p>
                                <a href="mailto:bakunzifiston@gmail.com" class="mt-1 block hover:text-white break-all">bakunzifiston@gmail.com</a>
                            </li>
                            <li>
                                <p class="text-xs font-semibold uppercase tracking-wider text-accent">Phone</p>
                                <a href="tel:0783092757" class="mt-1 block hover:text-white">0783092757</a>
                            </li>
                            <li>
                                <p class="text-xs font-semibold uppercase tracking-wider text-accent">Address</p>
                                <p class="mt-1">Kigali, Gasabo</p>
                            </li>
                        </ul>
                    </div>
                    <div class="overflow-hidden rounded-2xl shadow-brand aspect-[4/3]">
                        <img
                            src="{{ asset('images/banner/agents-workshop.jpg') }}"
                            alt="KoraLink Agents Academy"
                            class="w-full h-full object-cover object-center"
                            loading="lazy"
                        >
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
