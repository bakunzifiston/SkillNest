@php
    $role = $role ?? null;
@endphp

<div>
    <label for="name" class="block text-sm font-medium text-slate-700">Role name</label>
    <input type="text" name="name" id="name" value="{{ old('name', $role?->name) }}" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent" @disabled($role?->isSuperAdmin() && !auth()->user()->isSuperAdmin())>
    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="description" class="block text-sm font-medium text-slate-700">Description</label>
    <input type="text" name="description" id="description" value="{{ old('description', $role?->description) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent" placeholder="What this role is for">
    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="pt-2">
    <h3 class="text-sm font-semibold text-navy mb-1">Module access</h3>
    <p class="text-xs text-slate-500 mb-4">Enable a module to grant access, then choose whether the role can view, create, edit, or delete.</p>
    @include('admin.partials.permission-matrix', [
        'modules' => $modules,
        'groups' => $groups,
        'selectedPermissions' => old('permissions', $selectedPermissions ?? []),
        'customize' => true,
        'showCustomizeToggle' => false,
        'locked' => $locked ?? false,
    ])
</div>
