<section class="space-y-6">
    <header>
        <h2 class="font-display font-bold text-xl text-navy">
            {{ __('Delete Account') }}
        </h2>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2"
    >
        {{ __('Delete Account') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="font-display font-bold text-xl text-navy">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
