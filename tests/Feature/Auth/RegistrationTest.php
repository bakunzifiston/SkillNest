<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Location', false);
        $response->assertSee('Country', false);
        $response->assertSee('Rwanda', false);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'country' => 'Rwanda',
            'province' => 'Kigali City',
            'district' => 'Gasabo',
            'sector' => 'Remera',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'country' => 'Rwanda',
            'province' => 'Kigali City',
            'district' => 'Gasabo',
            'sector' => 'Remera',
        ]);
    }

    public function test_registration_requires_rwanda_location_hierarchy(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'missing-location@example.com',
            'country' => 'Rwanda',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['province', 'district', 'sector']);
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_rwanda_sector(): void
    {
        $response = $this->from('/register')->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'bad-sector@example.com',
            'country' => 'Rwanda',
            'province' => 'Kigali City',
            'district' => 'Gasabo',
            'sector' => 'NotARealSector',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('sector');
        $this->assertGuest();
    }

    public function test_non_rwanda_registration_does_not_require_hierarchy(): void
    {
        $response = $this->post('/register', [
            'first_name' => 'Kenya',
            'last_name' => 'User',
            'email' => 'kenya@example.com',
            'country' => 'Kenya',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $user = User::where('email', 'kenya@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Kenya', $user->country);
        $this->assertNull($user->province);
    }
}
