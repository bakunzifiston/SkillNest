<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Forgot your password?', false);
        $response->assertSee('Send Reset Link', false);
        $response->assertSee('Back to Login', false);
    }

    public function test_login_page_shows_forgot_password_link(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('password.request'), false)
            ->assertSee('Forgot your password?', false);
    }

    public function test_reset_password_link_can_be_requested_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status');
        $this->assertStringContainsString(
            'If an account exists with that email address',
            session('status')
        );

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_request_for_unknown_email_returns_generic_message(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', [
            'email' => 'nobody-exists@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status');
        $this->assertStringContainsString(
            'If an account exists with that email address',
            session('status')
        );

        Notification::assertNothingSent();
    }

    public function test_reset_password_email_contains_branded_content(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);

            $this->assertSame('Reset Your Password — '.config('app.name'), $mail->subject);
            $this->assertSame('emails.password-reset', $mail->view);

            return true;
        });
    }

    public function test_reset_password_screen_can_be_rendered_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $response = $this->get(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]));

            $response->assertOk();
            $response->assertSee('Reset password', false);
            $response->assertSee('Reset Password', false);
            $response->assertDontSee('invalid or has expired', false);

            return true;
        });
    }

    public function test_invalid_reset_link_shows_friendly_error_page(): void
    {
        $response = $this->get(route('password.reset', [
            'token' => 'invalid-token',
            'email' => 'student@example.com',
        ]));

        $response->assertOk();
        $response->assertSee('This password reset link is invalid or has expired.', false);
        $response->assertSee('Request New Reset Link', false);
    }

    public function test_reset_link_without_email_shows_friendly_error_page(): void
    {
        $this->get('/reset-password/some-token')
            ->assertOk()
            ->assertSee('This password reset link is invalid or has expired.', false);
    }

    public function test_password_validation_rejects_short_password(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $response = $this->from(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]))->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'short',
                'password_confirmation' => 'short',
            ]);

            $response->assertSessionHasErrors('password');

            return true;
        });
    }

    public function test_password_validation_rejects_mismatched_confirmation(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $response = $this->from(route('password.reset', [
                'token' => $notification->token,
                'email' => $user->email,
            ]))->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'different-password',
            ]);

            $response->assertSessionHasErrors('password');
            $this->assertSame(
                'The passwords do not match.',
                session('errors')->get('password')[0]
            );

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            $this->assertStringContainsString('Password reset successfully', session('status'));

            $user->refresh();
            $this->assertTrue(Hash::check('new-secure-password', $user->password));
            $this->assertFalse(Hash::check('old-password', $user->password));

            return true;
        });
    }

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'brand-new-password',
                'password_confirmation' => 'brand-new-password',
            ])->assertRedirect(route('login'));

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'old-password',
            ])->assertSessionHasErrors('email');

            $this->assertGuest();

            $this->post('/login', [
                'email' => $user->email,
                'password' => 'brand-new-password',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->assertAuthenticatedAs($user->fresh());

            return true;
        });
    }

    public function test_reset_token_cannot_be_reused_after_successful_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $token = $notification->token;

            $this->post('/reset-password', [
                'token' => $token,
                'email' => $user->email,
                'password' => 'first-new-password',
                'password_confirmation' => 'first-new-password',
            ])->assertRedirect(route('login'));

            $reuse = $this->post('/reset-password', [
                'token' => $token,
                'email' => $user->email,
                'password' => 'second-new-password',
                'password_confirmation' => 'second-new-password',
            ]);

            $reuse->assertRedirect(route('password.request'));
            $reuse->assertSessionHasErrors('email');

            $this->assertTrue(Hash::check('first-new-password', $user->fresh()->password));

            return true;
        });
    }

    public function test_expired_reset_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $token = Password::createToken($user);

        DB::table('password_reset_tokens')->where('email', $user->email)->update([
            'created_at' => now()->subHours(2),
        ]);

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]))
            ->assertOk()
            ->assertSee('This password reset link is invalid or has expired.', false);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect(route('password.request'));
    }
}
