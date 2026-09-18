@php
    $user = $user ?? null;
    $isEdit = (bool) $user;
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div>
        <label for="first_name" class="block text-sm font-medium text-slate-700">First name</label>
        <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user?->first_name ?? $user?->displayFirstName()) }}" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent">
        @error('first_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="last_name" class="block text-sm font-medium text-slate-700">Last name</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user?->last_name ?? $user?->displayLastName()) }}" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent">
        @error('last_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
    <input type="email" name="email" id="email" value="{{ old('email', $user?->email) }}" required class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent">
    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password {{ $isEdit ? '(optional)' : '' }}</label>
        <input type="password" name="password" id="password" autocomplete="new-password" @unless($isEdit) required @endunless class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent">
        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Confirm password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" @unless($isEdit) required @endunless class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent">
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    <div>
        <label for="role_id" class="block text-sm font-medium text-slate-700">Role / access level</label>
        <select
            name="role_id"
            id="role_id"
            class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-accent focus:ring-accent"
            onchange="window.dispatchEvent(new CustomEvent('role-changed', { detail: this.value }))"
        >
            <option value="">Student (no admin access)</option>
            @foreach($roles as $role)
                @if($role->isSuperAdmin() && !auth()->user()->isSuperAdmin())
                    @continue
                @endif
                <option value="{{ $role->id }}" @selected((string) old('role_id', $user?->role_id) === (string) $role->id)>{{ $role->name }}</option>
            @endforeach
        </select>
        @error('role_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        <p class="mt-1 text-xs text-slate-500">Students can use the public site. Staff roles unlock the admin modules you grant below.</p>
    </div>
    <div>
        <span class="block text-sm font-medium text-slate-700">Status</span>
        <input type="hidden" name="is_active" value="0">
        <label class="mt-2 inline-flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                class="rounded border-slate-300 text-primary focus:ring-primary"
                @checked(old('is_active', $user?->is_active ?? true))
            >
            <span class="text-sm text-navy">Active account</span>
        </label>
        @error('is_active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="pt-2">
    <h3 class="text-sm font-semibold text-navy mb-1">Module access</h3>
    <p class="text-xs text-slate-500 mb-4">Choose which admin modules this person can open, then grant View / Create / Edit / Delete where it applies.</p>
    @include('admin.partials.permission-matrix', [
        'modules' => $modules,
        'groups' => $groups,
        'selectedPermissions' => old('permissions', $selectedPermissions),
        'rolePermissionMap' => $rolePermissionMap,
        'customize' => (bool) old('customize_permissions', $customizePermissions ?? false),
        'showCustomizeToggle' => true,
        'locked' => false,
        'selectedRoleId' => old('role_id', $user?->role_id),
    ])
    @error('permissions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
