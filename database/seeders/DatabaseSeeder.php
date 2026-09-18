<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $superAdminRole = \App\Models\Role::ensureSystemRoles();

        $admin = User::updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => config('admin.name'),
                'password' => Hash::make(config('admin.password')),
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($superAdminRole);
        $admin->save();

        $this->call(CategoryCourseSeeder::class);
    }
}
