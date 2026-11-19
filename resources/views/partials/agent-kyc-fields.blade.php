{{-- Optional KYC details for Agents. $user is optional (profile page). --}}
@php($user = $user ?? null)
<div class="space-y-5">
    <div>
        <x-input-label for="phone" :value="__('Phone number')" class="text-slate-700" />
        <x-text-input id="phone" name="phone" type="tel" class="mt-1.5 block w-full" :value="old('phone', $user?->phone)" placeholder="+250 788 000 000" autocomplete="tel" />
        <x-input-error class="mt-1.5" :messages="$errors->get('phone')" />
    </div>

    <div>
        <x-input-label for="national_id" :value="__('National ID number')" class="text-slate-700" />
        <x-text-input id="national_id" name="national_id" type="text" class="mt-1.5 block w-full" :value="old('national_id', $user?->national_id)" autocomplete="off" inputmode="text" />
        <x-input-error class="mt-1.5" :messages="$errors->get('national_id')" />
    </div>

    <div class="grid sm:grid-cols-2 gap-5">
        <div>
            <x-input-label for="date_of_birth" :value="__('Date of birth')" class="text-slate-700" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1.5 block w-full" :value="old('date_of_birth', $user?->date_of_birth?->format('Y-m-d'))" max="{{ now()->subDay()->format('Y-m-d') }}" autocomplete="bday" />
            <x-input-error class="mt-1.5" :messages="$errors->get('date_of_birth')" />
        </div>

        <div>
            <x-input-label for="gender" :value="__('Gender')" class="text-slate-700" />
            <select id="gender" name="gender" class="block mt-1.5 w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                <option value="">{{ __('Select') }}</option>
                @foreach(\App\Support\AgentKyc::genders() as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $user?->gender) === $value)>{{ __($label) }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-1.5" :messages="$errors->get('gender')" />
        </div>
    </div>
</div>
