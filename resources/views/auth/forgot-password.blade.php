<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">Forgot your password?</h1>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            Enter the email address associated with your account and we’ll send you a link to reset your password.
        </p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" class="text-slate-700" />
            <x-text-input
                id="email"
                class="block mt-1.5 w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                maxlength="255"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
        >
            Send Reset Link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-accent-dark transition">
            ← Back to Login
        </a>
    </p>
</x-guest-layout>
