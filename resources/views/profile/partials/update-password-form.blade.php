<section>
    <header>
        <h2 class="font-display font-bold text-xl text-navy">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-8 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="text-slate-700" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1.5 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <x-input-label for="update_password_password" :value="__('New Password')" class="text-slate-700" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="text-slate-700" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1.5 block w-full" autocomplete="new-password" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-1">
            <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-accent text-white font-semibold hover:bg-accent-dark transition focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
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
