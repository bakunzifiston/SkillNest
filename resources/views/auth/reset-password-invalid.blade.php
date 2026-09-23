<x-guest-layout>
    <div class="mb-8 text-center sm:text-left">
        <div class="mx-auto sm:mx-0 mb-5 inline-flex h-12 w-12 items-center justify-center rounded-xl bg-accent-light text-accent-darker">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">
            This password reset link is invalid or has expired.
        </h1>
        <p class="mt-3 text-sm text-slate-600 leading-relaxed">
            Please request a new password reset link to continue.
        </p>
    </div>

    <a
        href="{{ route('password.request') }}"
        class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
    >
        Request New Reset Link
    </a>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-accent-dark transition">
            ← Back to Login
        </a>
    </p>
</x-guest-layout>
