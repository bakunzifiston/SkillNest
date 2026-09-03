@extends('layouts.site')

@section('title', 'About Us')

@section('content')
    <section class="relative text-white overflow-hidden">
        <div class="absolute inset-0">
            <img
                src="{{ asset('images/banner/agents-training.jpg') }}"
                alt="KoraLink Agents Academy training"
                class="w-full h-full object-cover object-center"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-navy/90 via-navy/70 to-navy/40"></div>
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
            <p class="text-sm font-semibold uppercase tracking-wider text-accent">Digital Jobs for Youth in Health</p>
            <h1 class="mt-3 font-display font-bold text-4xl sm:text-5xl tracking-tight">KoraLink <span class="text-accent">Agents Academy</span></h1>
            <p class="mt-5 max-w-2xl text-lg text-slate-200 leading-relaxed">The official e-learning platform of the Digital Jobs for Youth in Health (KoraLink Agents) program, dedicated to the success of our Digital Community Champions.</p>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="relative overflow-hidden rounded-2xl shadow-brand aspect-[4/3] lg:aspect-auto lg:min-h-[22rem]">
                    <img
                        src="{{ asset('images/banner/agents-workshop.jpg') }}"
                        alt="KoraLink agents in a training workshop"
                        class="absolute inset-0 w-full h-full object-cover object-center"
                        loading="lazy"
                    >
                </div>
                <div>
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy">Become a Digital Community Champion</h2>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">The KoraLink Agents e-learning platform is your gateway to becoming a true Digital Community Champion under the Digital Jobs for Youth in Health program. Here, you’ll gain practical digital skills, connect with other changemakers, and take the next step toward improving community health through innovation and technology.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl mb-10">
                <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy">What you’ll learn</h2>
                <p class="mt-4 text-lg text-slate-600">On this platform, you will find courses that help you learn how to use the Marketplace effectively, provide excellent service to your customers, and grow your business.</p>
            </div>
            <div class="grid sm:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-accent">Marketplace</p>
                    <h3 class="mt-2 font-display font-semibold text-xl text-navy">Use it effectively</h3>
                    <p class="mt-3 text-slate-600">Learn how to use the Marketplace with confidence in your day-to-day work as a KoraLink Agent.</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-accent">Service</p>
                    <h3 class="mt-2 font-display font-semibold text-xl text-navy">Serve customers well</h3>
                    <p class="mt-3 text-slate-600">Build the skills to provide excellent service to your customers in the community.</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="text-sm font-semibold uppercase tracking-wider text-accent">Growth</p>
                    <h3 class="mt-2 font-display font-semibold text-xl text-navy">Grow your business</h3>
                    <p class="mt-3 text-slate-600">Take the next step toward growing your business through practical digital skills.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 lg:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center">
                <div class="order-2 lg:order-1">
                    <h2 class="font-display font-bold text-3xl lg:text-4xl text-navy">Built for KoraLink Agents</h2>
                    <p class="mt-6 text-lg text-slate-600 leading-relaxed">This academy exists to support Digital Community Champions: practical learning, a community of changemakers, and a clear path to improving community health with innovation and technology.</p>
                    <div class="mt-8 flex flex-wrap gap-4">
                        <a href="{{ route('courses.index') }}" class="inline-flex items-center px-6 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition">Explore courses</a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 rounded-xl border border-slate-300 text-navy hover:bg-slate-50 font-medium transition">Contact us</a>
                    </div>
                </div>
                <div class="relative overflow-hidden rounded-2xl shadow-brand aspect-[4/3] lg:aspect-auto lg:min-h-[22rem] order-1 lg:order-2">
                    <img
                        src="{{ asset('images/banner/agents-field.jpg') }}"
                        alt="KoraLink agents collaborating in the community"
                        class="absolute inset-0 w-full h-full object-cover object-center"
                        loading="lazy"
                    >
                </div>
            </div>
        </div>
    </section>
@endsection
