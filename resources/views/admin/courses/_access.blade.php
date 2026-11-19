{{-- Who may see and enroll in this course. Expects $course (or null) and $partnerOrganizations. --}}
@php
    $selectedLevel = old('access_level', $course?->access_level ?: \App\Models\Course::ACCESS_ALL);
@endphp
<div x-data="{ level: '{{ $selectedLevel }}' }" class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-4">
    <div>
        <label for="access_level" class="block text-sm font-medium text-gray-700">{{ __('Who can access this course') }}</label>
        <select name="access_level" id="access_level" x-model="level" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent max-w-md">
            @foreach(\App\Models\Course::accessLevels() as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        @error('access_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div x-show="level === 'partner'" x-cloak>
        <label for="partner_organization_id" class="block text-sm font-medium text-gray-700">{{ __('Partner organization') }}</label>
        <select name="partner_organization_id" id="partner_organization_id" x-bind:disabled="level !== 'partner'" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent max-w-md">
            <option value="">{{ __('Select organization') }}</option>
            @foreach($partnerOrganizations as $org)
                <option value="{{ $org->id }}" @selected((string) old('partner_organization_id', $course?->partner_organization_id) === (string) $org->id)>{{ $org->name }}{{ $org->is_active ? '' : ' '.__('(inactive)') }}</option>
            @endforeach
        </select>
        @error('partner_organization_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <p class="text-xs text-gray-500">{{ __('Learners outside the audience will not see this course, cannot enroll, and cannot open it by URL. Learners already enrolled keep their access.') }}</p>
</div>
