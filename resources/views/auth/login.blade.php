<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">{{ __('Log in') }}</h1>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            Welcome back. Sign in to continue learning.
        </p>
    </div>

    @if(session('status') && str_contains((string) session('status'), 'Password reset successfully'))
        <div class="mb-5 rounded-xl border border-success-muted bg-success-light px-4 py-3 text-sm text-success-darker" role="status">
            <p class="font-semibold">Password reset successfully</p>
            <p class="mt-1">You can now sign in using your new password.</p>
        </div>
    @else
        <x-auth-session-status class="mb-5" :status="session('status')" />
    @endif

    @if(session('url.intended'))
        <div class="mb-5 rounded-xl border border-primary-muted/50 bg-primary-light px-4 py-3 text-sm text-navy">
            Sign in or create an account to continue (for example, to enroll in a course).
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-primary hover:text-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary rounded">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <label for="remember_me" class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-primary shadow-sm focus:ring-primary" name="remember">
                <span class="text-sm text-slate-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            {{ __('Log in') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        New to {{ config('app.name') }}?
        <a href="{{ route('register') }}" class="font-semibold text-primary hover:text-accent-dark transition">
            Sign up as a learner
        </a>
    </p>
</x-guest-layout>
