<section>
    <header>
        <h2 class="font-display font-bold text-xl text-navy">
            {{ __('Profile Information') }}
        </h2>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('patch')

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <x-input-label for="first_name" :value="__('First name')" class="text-slate-700" />
                <x-text-input id="first_name" name="first_name" type="text" class="mt-1.5 block w-full" :value="old('first_name', $user->displayFirstName())" required autofocus autocomplete="given-name" />
                <x-input-error class="mt-1.5" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last name')" class="text-slate-700" />
                <x-text-input id="last_name" name="last_name" type="text" class="mt-1.5 block w-full" :value="old('last_name', $user->displayLastName())" required autocomplete="family-name" />
                <x-input-error class="mt-1.5" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" class="text-slate-700" />
            <x-text-input id="email" name="email" type="email" class="mt-1.5 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-accent-muted bg-accent-light px-4 py-3">
                    <p class="text-sm text-accent-darker">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="font-semibold underline hover:no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-accent rounded">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-success-dark">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-success-dark"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
