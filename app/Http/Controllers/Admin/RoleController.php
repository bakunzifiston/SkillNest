<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Support\AdminAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->with('permissions')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $kpis = [
            [
                'label' => 'Roles',
                'value' => $roles->count(),
                'icon' => 'shield',
                'tone' => 'primary',
            ],
            [
                'label' => 'Custom',
                'value' => $roles->where('is_system', false)->count(),
                'icon' => 'users',
                'tone' => 'accent',
            ],
            [
                'label' => 'Assigned users',
                'value' => $roles->sum('users_count'),
                'icon' => 'user',
                'tone' => 'success',
            ],
            [
                'label' => 'Modules',
                'value' => count(AdminAccess::modules()),
                'icon' => 'settings',
                'tone' => 'slate',
            ],
        ];

        return view('admin.roles.index', compact('roles', 'kpis'));
    }

    public function create(): View
    {
        return view('admin.roles.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $role = Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('admin.roles.edit', array_merge($this->formData($role), compact('role')));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $this->validated($request, $role);

        if ($role->isSuperAdmin() && ! $request->user()?->isSuperAdmin()) {
            abort(403, 'Only a Super Admin can edit this role.');
        }

        $role->name = $data['name'];
        $role->description = $data['description'] ?? null;

        if (! $role->is_system) {
            $role->slug = $data['slug'];
        }

        $role->save();
        $role->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('admin.roles.edit', $role)
            ->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        $role->users()->update(['role_id' => null, 'is_admin' => false]);
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(?Role $role = null): array
    {
        return [
            'modules' => AdminAccess::modules(),
            'groups' => config('admin-modules.groups', []),
            'selectedPermissions' => $role?->permissionMap() ?? [],
            'locked' => (bool) $role?->isSuperAdmin(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['array'],
            'permissions.*.*' => ['string'],
        ]);

        $slug = $role?->is_system
            ? $role->slug
            : Str::slug($data['name']);

        $request->merge(['slug' => $slug]);

        $request->validate([
            'slug' => ['required', 'string', 'max:100', Rule::unique('roles', 'slug')->ignore($role?->id)],
        ]);

        $data['slug'] = $slug;

        return $data;
    }
}
