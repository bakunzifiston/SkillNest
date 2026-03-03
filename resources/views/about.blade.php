@extends('layouts.site')

@section('title', 'About Us')

@section('content')
    <section class="bg-white border-b border-slate-200 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">About SkillNest</h1>
            <p class="mt-2 text-slate-600">We’re on a mission to make quality learning accessible to everyone.</p>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl prose prose-slate prose-lg">
                <p class="text-slate-600 leading-relaxed">
                    SkillNest is an e-learning platform built to help you learn in-demand skills at your own pace. Whether you’re starting a new career, leveling up at work, or exploring a hobby, we offer clear, practical courses designed by industry practitioners.
                </p>
                <h2 class="font-display font-bold text-xl text-slate-900 mt-10">What we offer</h2>
                <ul class="space-y-2 text-slate-600">
                    <li><strong class="text-slate-800">Expert-led courses</strong> — Learn from people who use these skills every day.</li>
                    <li><strong class="text-slate-800">Hands-on projects</strong> — Apply what you learn with real-world exercises.</li>
                    <li><strong class="text-slate-800">Flexible learning</strong> — Study when it suits you, from any device.</li>
                    <li><strong class="text-slate-800">Clear paths</strong> — Follow structured paths from beginner to advanced.</li>
                </ul>
                <h2 class="font-display font-bold text-xl text-slate-900 mt-10">Our mission</h2>
                <p class="text-slate-600 leading-relaxed">
                    We believe everyone deserves access to high-quality education. By combining clear instruction, practical projects, and a supportive community, we help learners worldwide reach their goals and grow their careers.
                </p>
                <p class="mt-6">
                    <a href="{{ route('courses.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Explore courses</a>
                </p>
            </div>
        </div>
    </section>
@endsection
