<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'is_admin' => true,
            'is_active' => true,
        ])->afterCreating(function (\App\Models\User $user) {
            $user->assignRole(\App\Models\Role::ensureSystemRoles());
            $user->save();
        });
    }

    /**
     * @param  array<string, list<string>>  $permissions
     */
    public function staff(array $permissions): static
    {
        return $this->state(fn () => [
            'is_admin' => false,
            'is_active' => true,
        ])->afterCreating(function (\App\Models\User $user) use ($permissions) {
            $role = \App\Models\Role::query()->create([
                'name' => 'Staff '.$user->id,
                'slug' => 'staff-'.$user->id,
                'description' => 'Generated staff role',
                'is_system' => false,
            ]);
            $role->syncPermissions($permissions);
            $user->assignRole($role);
            $user->save();
        });
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
