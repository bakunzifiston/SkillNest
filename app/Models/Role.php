<?php

namespace App\Models;

use App\Support\AdminAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Role extends Model
{
    public const SUPER_ADMIN = 'super-admin';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Role $role) {
            if (blank($role->slug)) {
                $role->slug = Str::slug($role->name);
            }
        });
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }

    /**
     * @return array<string, list<string>>
     */
    public function permissionMap(): array
    {
        if ($this->isSuperAdmin()) {
            return AdminAccess::allPermissions();
        }

        $this->loadMissing('permissions');

        return AdminAccess::mapPermissions($this->permissions);
    }

    /**
     * @param  array<string, mixed>  $permissions
     */
    public function syncPermissions(array $permissions): void
    {
        $granted = $this->isSuperAdmin()
            ? AdminAccess::allPermissions()
            : AdminAccess::sanitize($permissions);

        $this->permissions()->delete();

        $rows = [];

        foreach ($granted as $module => $actions) {
            foreach ($actions as $action) {
                $rows[] = [
                    'module' => $module,
                    'action' => $action,
                ];
            }
        }

        if ($rows !== []) {
            $this->permissions()->createMany($rows);
        }
    }

    public static function superAdmin(): ?self
    {
        return static::query()->where('slug', self::SUPER_ADMIN)->first();
    }

    public static function ensureSystemRoles(): self
    {
        $role = static::query()->firstOrCreate(
            ['slug' => self::SUPER_ADMIN],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to every admin module.',
                'is_system' => true,
            ]
        );

        $role->syncPermissions(AdminAccess::allPermissions());

        return $role->fresh(['permissions']) ?? $role;
    }
}
