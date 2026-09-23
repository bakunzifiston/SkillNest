<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">Reset password</h1>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            Choose a new password for your account. Use at least 8 characters.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
            <x-text-input
                id="email"
                class="block mt-1.5 w-full bg-slate-50"
                type="email"
                name="email"
                value="{{ old('email', $email) }}"
                required
                readonly
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('New password')" class="text-slate-700" />
            <x-text-input
                id="password"
                class="block mt-1.5 w-full"
                type="password"
                name="password"
                required
                autofocus
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm new password')" class="text-slate-700" />
            <x-text-input
                id="password_confirmation"
                class="block mt-1.5 w-full"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2"
        >
            Reset Password
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-accent-dark transition">
            ← Back to Login
        </a>
    </p>
</x-guest-layout>
