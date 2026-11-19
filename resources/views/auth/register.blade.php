<x-guest-layout>
    <div class="mb-8">
        <h1 class="font-display font-bold text-2xl text-navy tracking-tight">{{ __('Create an account') }}</h1>
        <p class="mt-2 text-sm text-slate-600 leading-relaxed">
            {{ __('Join as a learner and start building practical digital skills.') }}
        </p>
    </div>

    @php
        $selectedType = old('learner_type', $defaultType ?? 'agent');
        $hasOrgs = ($partnerOrganizations ?? collect())->isNotEmpty();
    @endphp
    <form method="POST" action="{{ route('register') }}" class="space-y-5" x-data="{ type: '{{ $hasOrgs ? $selectedType : 'agent' }}' }">
        @csrf

        <div>
            <p class="text-sm font-semibold text-navy mb-2">{{ __('I am registering as') }}</p>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="type === 'agent' ? 'border-primary bg-primary-light' : 'border-slate-200 hover:border-slate-300'">
                    <input type="radio" name="learner_type" value="agent" x-model="type" class="mt-1 text-primary focus:ring-accent">
                    <span>
                        <span class="block text-sm font-semibold text-navy">{{ __('Agent') }}</span>
                        <span class="block text-xs text-slate-500">{{ __('Independent learner') }}</span>
                    </span>
                </label>
                @if($hasOrgs)
                    <label class="flex items-start gap-3 rounded-xl border p-3 cursor-pointer transition" :class="type === 'partner' ? 'border-primary bg-primary-light' : 'border-slate-200 hover:border-slate-300'">
                        <input type="radio" name="learner_type" value="partner" x-model="type" class="mt-1 text-primary focus:ring-accent">
                        <span>
                            <span class="block text-sm font-semibold text-navy">{{ __('Partner') }}</span>
                            <span class="block text-xs text-slate-500">{{ __('Through a partner organization') }}</span>
                        </span>
                    </label>
                @endif
            </div>
            <x-input-error :messages="$errors->get('learner_type')" class="mt-1.5" />

            @if($hasOrgs)
                <div x-show="type === 'partner'" x-cloak class="mt-4">
                    <x-input-label for="partner_organization_id" :value="__('Partner organization')" class="text-slate-700" />
                    <select id="partner_organization_id" name="partner_organization_id" x-bind:required="type === 'partner'" x-bind:disabled="type !== 'partner'" class="block mt-1.5 w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                        <option value="">{{ __('Select your organization') }}</option>
                        @foreach($partnerOrganizations as $org)
                            <option value="{{ $org->id }}" @selected((string) old('partner_organization_id', $defaultOrganizationId ?? null) === (string) $org->id)>{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('partner_organization_id')" class="mt-1.5" />

                    <div class="mt-4">
                        <x-input-label for="partner_code" :value="__('Organization access code')" class="text-slate-700" />
                        <x-text-input id="partner_code" class="block mt-1.5 w-full uppercase tracking-widest" type="text" name="partner_code" :value="old('partner_code')" x-bind:required="type === 'partner'" x-bind:disabled="type !== 'partner'" autocomplete="off" />
                        <p class="mt-1 text-xs text-slate-500">{{ __('Ask your organization for its access code.') }}</p>
                        <x-input-error :messages="$errors->get('partner_code')" class="mt-1.5" />
                    </div>
                </div>
            @endif
        </div>

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

        <div class="pt-1">
            <p class="text-sm font-semibold text-navy mb-3">{{ __('Location') }}</p>
            @include('partials.location-fields')
        </div>

        <div x-show="type === 'agent'" x-cloak class="pt-1">
            <div class="mb-3">
                <p class="text-sm font-semibold text-navy">{{ __('Additional details') }}</p>
            </div>
            <fieldset x-bind:disabled="type !== 'agent'" style="display: contents;">
                @include('partials.agent-kyc-fields', ['user' => null])
            </fieldset>
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
