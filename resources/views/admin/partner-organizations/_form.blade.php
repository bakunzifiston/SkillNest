<div>
    <label for="name" class="block text-sm font-medium text-navy">Organization name</label>
    <input type="text" name="name" id="name" value="{{ old('name', $organization?->name) }}" required maxlength="255" class="mt-1 block w-full rounded-xl border-slate-300 shadow-sm focus:border-primary focus:ring-primary">
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <input type="hidden" name="is_active" value="0">
    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" @checked((bool) old('is_active', $organization?->is_active ?? true)) class="rounded border-gray-300 text-primary focus:ring-accent">
        <span class="text-sm text-slate-700">Active (learners can register under it)</span>
    </label>
    <p class="mt-1 text-xs text-slate-400">Deactivating hides it from the registration form. Existing learners and courses keep their link.</p>
</div>
