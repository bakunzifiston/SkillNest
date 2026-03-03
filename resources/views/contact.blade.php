@extends('layouts.site')

@section('title', 'Contact')

@section('content')
    <section class="bg-white border-b border-slate-200 py-12 lg:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="font-display font-bold text-3xl lg:text-4xl text-slate-900">Contact Us</h1>
            <p class="mt-2 text-slate-600">Have a question or feedback? We’d love to hear from you.</p>
        </div>
    </section>

    <section class="py-16 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16">
                <div>
                    <h2 class="font-display font-semibold text-xl text-slate-900 mb-4">Get in touch</h2>
                    <p class="text-slate-600 mb-6">Send us a message and we’ll get back to you as soon as we can.</p>
                    <form action="{{ route('contact.submit') }}" method="post" class="space-y-5">
                        @csrf
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Your name">
                            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="you@example.com">
                            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="What is this about?">
                            @error('subject')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 mb-1">Message</label>
                            <textarea name="message" id="message" rows="4" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:border-amber-500" placeholder="Your message">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-amber-500 text-white font-semibold hover:bg-amber-600 transition">Send message</button>
                    </form>
                    @if(session('success'))
                        <p class="mt-4 text-green-600 font-medium">{{ session('success') }}</p>
                    @endif
                </div>
                <div>
                    <h2 class="font-display font-semibold text-xl text-slate-900 mb-4">Other ways to reach us</h2>
                    <div class="space-y-4 text-slate-600">
                        <p><span class="font-medium text-slate-800">Email:</span> bakunzifiston@gmail.com</p>
                        <p><span class="font-medium text-slate-800">Phone:</span> 0783092757</p>
                        <p><span class="font-medium text-slate-800">Address:</span> Kigali, Gasabo</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
