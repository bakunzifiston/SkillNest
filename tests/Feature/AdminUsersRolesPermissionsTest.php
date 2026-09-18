<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersRolesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_admin_panel(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_inactive_staff_cannot_access_admin_panel(): void
    {
        $user = User::factory()->staff([
            'dashboard' => ['view'],
            'courses' => ['view'],
        ])->inactive()->create();

        $this->actingAs($user)
            ->get(route('admin.courses.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_open_users_and_roles(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Add user')
            ->assertSee('Roles & permissions');

        $this->actingAs($admin)
            ->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee('Super Admin');
    }

    public function test_role_without_module_access_is_blocked_from_that_module(): void
    {
        $user = User::factory()->staff([
            'dashboard' => ['view'],
            'courses' => ['view', 'create'],
        ])->create();

        $this->actingAs($user)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('Add course')
            ->assertDontSee('Users');

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.courses.create'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.categories.index'))
            ->assertForbidden();
    }

    public function test_view_only_role_cannot_create_or_delete_courses(): void
    {
        $user = User::factory()->staff([
            'dashboard' => ['view'],
            'courses' => ['view'],
        ])->create();

        $this->actingAs($user)
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertDontSee('Add course');

        $this->actingAs($user)
            ->get(route('admin.courses.create'))
            ->assertForbidden();
    }

    public function test_admin_can_create_user_with_a_role(): void
    {
        $admin = User::factory()->admin()->create();
        $role = Role::query()->create([
            'name' => 'Course Editor',
            'slug' => 'course-editor',
            'description' => 'Manages courses',
        ]);
        $role->syncPermissions([
            'dashboard' => ['view'],
            'courses' => ['view', 'create', 'edit'],
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'ada@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_id' => $role->id,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $user = User::query()->where('email', 'ada@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_active);
        $this->assertSame($role->id, $user->role_id);
        $this->assertTrue($user->hasPermission('courses', 'edit'));
        $this->assertFalse($user->hasPermission('courses', 'delete'));
        $this->assertFalse($user->hasPermission('users', 'view'));
    }

    public function test_admin_can_create_custom_role_and_permissions_are_enforced(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.roles.store'), [
                'name' => 'Settings Viewer',
                'description' => 'Can read settings',
                'permissions' => [
                    'dashboard' => ['view'],
                    'settings' => ['view'],
                ],
            ])
            ->assertRedirect();

        $role = Role::query()->where('slug', 'settings-viewer')->first();
        $this->assertNotNull($role);
        $this->assertSame(['view'], $role->permissionMap()['settings'] ?? []);

        $staff = User::factory()->create([
            'is_active' => true,
        ]);
        $staff->assignRole($role);
        $staff->save();

        $this->actingAs($staff)
            ->get(route('admin.settings.edit'))
            ->assertOk();

        $this->actingAs($staff)
            ->put(route('admin.settings.update'), [
                'site_name' => 'Nope',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.courses.index'))
            ->assertForbidden();
    }

    public function test_user_custom_permissions_override_role(): void
    {
        $admin = User::factory()->admin()->create();
        $role = Role::query()->create([
            'name' => 'Catalog',
            'slug' => 'catalog',
        ]);
        $role->syncPermissions([
            'dashboard' => ['view'],
            'courses' => ['view', 'create', 'edit', 'delete'],
        ]);

        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);
        $user->save();

        $this->actingAs($admin)
            ->put(route('admin.users.update', $user), [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role_id' => $role->id,
                'is_active' => '1',
                'customize_permissions' => '1',
                'permissions' => [
                    'dashboard' => ['view'],
                    'courses' => ['view'],
                ],
            ])
            ->assertRedirect();

        $user->refresh();
        $this->assertTrue($user->hasCustomPermissions());
        $this->assertTrue($user->hasPermission('courses', 'view'));
        $this->assertFalse($user->hasPermission('courses', 'delete'));
    }

    public function test_cannot_delete_the_last_super_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $manager = User::factory()->staff([
            'dashboard' => ['view'],
            'users' => ['view', 'delete'],
        ])->create();

        $this->actingAs($manager)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_system_role_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $role = Role::ensureSystemRoles();

        $this->actingAs($admin)
            ->from(route('admin.roles.index'))
            ->delete(route('admin.roles.destroy', $role))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['slug' => Role::SUPER_ADMIN]);
    }
}
