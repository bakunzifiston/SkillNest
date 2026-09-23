<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">{{ __('Create an account') }}</h1>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            Join as a learner and start building practical digital skills.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <x-input-label for="first_name" :value="__('First name')" class="text-slate-700" />
                <x-text-input id="first_name" class="block mt-1.5 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last name')" class="text-slate-700" />
                <x-text-input id="last_name" class="block mt-1.5 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-1.5" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" class="text-slate-700" />
            <x-text-input id="password" class="block mt-1.5 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-slate-700" />
            <x-text-input id="password_confirmation" class="block mt-1.5 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-3 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
            {{ __('Register') }}
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-600">
        {{ __('Already registered?') }}
        <a href="{{ route('login') }}" class="font-semibold text-primary hover:text-accent-dark transition">
            {{ __('Log in') }}
        </a>
    </p>
</x-guest-layout>
