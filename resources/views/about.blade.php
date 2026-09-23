@extends('layouts.site')

@section('title', 'About')

@section('content')
    <section class="relative overflow-hidden bg-navy text-white">
        <div class="absolute inset-0">
            <img
                src="{{ asset('images/banner/agents-training.jpg') }}"
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
                    <li class="text-white font-medium">About</li>
                </ol>
            </nav>
            <p class="text-xs sm:text-sm font-semibold uppercase tracking-[0.18em] text-accent">Digital Jobs for Youth in Health</p>
            <h1 class="mt-4 font-display font-bold text-4xl sm:text-5xl tracking-tight max-w-3xl">
                About KoraLink Agents Academy
            </h1>
            <p class="mt-5 max-w-2xl text-lg text-slate-200 leading-relaxed">
                The official learning platform supporting Digital Community Champions with practical digital skills, marketplace readiness, and community impact.
            </p>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                <div class="lg:col-span-6">
                    <div class="relative max-w-lg mx-auto lg:max-w-none">
                        <div class="relative aspect-[4/3] rounded-2xl overflow-hidden shadow-brand ring-1 ring-slate-200">
                            <img
                                src="{{ asset('images/banner/agents-workshop.jpg') }}"
                                alt="KoraLink agents collaborating in a training workshop"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="lazy"
                            >
                        </div>
                        <div class="absolute -bottom-5 -right-2 sm:right-4 w-[42%] aspect-[4/3] rounded-xl overflow-hidden shadow-xl ring-4 ring-white">
                            <img
                                src="{{ asset('images/banner/agents-field.jpg') }}"
                                alt="KoraLink agents working in the community"
                                class="absolute inset-0 w-full h-full object-cover object-center"
                                loading="lazy"
                            >
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-6 pt-6 lg:pt-0">
                    <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Become a Digital Community Champion</h2>
                    <p class="mt-5 text-slate-600 leading-relaxed">
                        This platform helps agents strengthen digital skills, use digital marketplaces with confidence, deliver better services, and contribute to stronger community health outcomes through innovation and technology.
                    </p>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        Learning is practical and self-paced — so you can build capability while continuing your work in the field.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">What you’ll learn</h2>
                <p class="mt-3 text-slate-600 leading-relaxed">
                    Courses focus on the skills agents need every day — from marketplace tools to customer service and business growth.
                </p>
            </div>
            <div class="grid sm:grid-cols-3 gap-5">
                <div class="rounded-2xl bg-white border border-slate-200 p-6">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M5 7v12a2 2 0 002 2h10a2 2 0 002-2V7M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/></svg>
                    </span>
                    <h3 class="mt-4 font-display font-semibold text-lg text-navy">Marketplace skills</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">Use the Marketplace effectively in your day-to-day work as a KoraLink Agent.</p>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200 p-6">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 21v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2M13 7a4 4 0 11-8 0 4 4 0 018 0zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    </span>
                    <h3 class="mt-4 font-display font-semibold text-lg text-navy">Customer service</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">Provide excellent service to customers and communities you support.</p>
                </div>
                <div class="rounded-2xl bg-white border border-slate-200 p-6">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary-light text-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </span>
                    <h3 class="mt-4 font-display font-semibold text-lg text-navy">Business growth</h3>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">Apply digital skills to grow your work and create greater impact.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl bg-primary-light border border-primary-muted/40 px-6 py-10 sm:px-10 lg:px-14 lg:py-12">
                <div class="grid lg:grid-cols-12 gap-8 lg:gap-10 items-center">
                    <div class="lg:col-span-8">
                        <h2 class="font-display font-bold text-2xl lg:text-3xl text-navy">Ready to start learning?</h2>
                        <p class="mt-3 text-slate-600 leading-relaxed max-w-2xl">
                            Explore practical courses designed for KoraLink Agents, or reach out if you have questions about the Academy or the program.
                        </p>
                    </div>
                    <div class="lg:col-span-4 flex flex-wrap lg:justify-end gap-3">
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                            Explore courses
                        </a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-primary/20 bg-white text-navy font-medium hover:bg-slate-50 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                            Contact us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
