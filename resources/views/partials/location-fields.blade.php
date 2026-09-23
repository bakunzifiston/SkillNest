@php
    use App\Support\RwandaLocations;

    $selectClass = $selectClass ?? 'mt-1.5 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-primary focus:ring-primary';
    $labelClass = $labelClass ?? 'block text-sm font-medium text-slate-700';
    $user = $user ?? null;
    $defaultCountry = old('country', $user?->country ?? RwandaLocations::DEFAULT_COUNTRY);
    $defaultProvince = old('province', $user?->province);
    $defaultDistrict = old('district', $user?->district);
    $defaultSector = old('sector', $user?->sector);
    $tree = RwandaLocations::tree();
    $countries = RwandaLocations::countries();
@endphp

<div
    class="space-y-5"
    x-data="{
        country: @js($defaultCountry),
        province: @js($defaultProvince ?? ''),
        district: @js($defaultDistrict ?? ''),
        sector: @js($defaultSector ?? ''),
        tree: @js($tree),
        get provinces() { return Object.keys(this.tree); },
        get districts() { return this.province && this.tree[this.province] ? Object.keys(this.tree[this.province]) : []; },
        get sectors() { return (this.province && this.district && this.tree[this.province] && this.tree[this.province][this.district]) ? this.tree[this.province][this.district] : []; },
        get isRwanda() { return this.country === 'Rwanda'; },
        onCountryChange() {
            if (!this.isRwanda) {
                this.province = '';
                this.district = '';
                this.sector = '';
            }
        },
        onProvinceChange() {
            this.district = '';
            this.sector = '';
        },
        onDistrictChange() {
            this.sector = '';
        }
    }"
>
    <div>
        <label for="country" class="{{ $labelClass }}">Country</label>
        <select
            name="country"
            id="country"
            required
            x-model="country"
            @change="onCountryChange()"
            class="{{ $selectClass }}"
        >
            @foreach($countries as $countryOption)
                <option value="{{ $countryOption }}" @selected($defaultCountry === $countryOption)>{{ $countryOption }}</option>
            @endforeach
        </select>
        @error('country')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-3 gap-5" x-show="isRwanda" x-cloak>
        <div>
            <label for="province" class="{{ $labelClass }}">Province</label>
            <select
                name="province"
                id="province"
                x-model="province"
                @change="onProvinceChange()"
                :required="isRwanda"
                class="{{ $selectClass }}"
            >
                <option value="">Select province</option>
                <template x-for="name in provinces" :key="name">
                    <option :value="name" x-text="name" :selected="province === name"></option>
                </template>
            </select>
            @error('province')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="district" class="{{ $labelClass }}">District</label>
            <select
                name="district"
                id="district"
                x-model="district"
                @change="onDistrictChange()"
                :required="isRwanda"
                :disabled="!province"
                class="{{ $selectClass }}"
            >
                <option value="">Select district</option>
                <template x-for="name in districts" :key="name">
                    <option :value="name" x-text="name" :selected="district === name"></option>
                </template>
            </select>
            @error('district')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="sector" class="{{ $labelClass }}">Sector</label>
            <select
                name="sector"
                id="sector"
                x-model="sector"
                :required="isRwanda"
                :disabled="!district"
                class="{{ $selectClass }}"
            >
                <option value="">Select sector</option>
                <template x-for="name in sectors" :key="name">
                    <option :value="name" x-text="name" :selected="sector === name"></option>
                </template>
            </select>
            @error('sector')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Keep empty location fields when not Rwanda so old() / mass assignment stay clean --}}
    <template x-if="!isRwanda">
        <div>
            <input type="hidden" name="province" value="">
            <input type="hidden" name="district" value="">
            <input type="hidden" name="sector" value="">
        </div>
    </template>
</div>
