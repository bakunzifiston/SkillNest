<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class MailConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_mail_test_command_warns_that_the_log_mailer_does_not_deliver(): void
    {
        config(['mail.default' => 'log']);

        $this->artisan('mail:test', ['email' => 'someone@example.com'])
            ->expectsOutputToContain('will NOT be delivered')
            ->expectsOutputToContain('only written to the log file')
            ->assertSuccessful();
    }

    public function test_mail_test_command_lists_every_smtp_config_problem(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.username' => 'null',
            'mail.mailers.smtp.password' => null,
            'mail.mailers.smtp.scheme' => 'tls',
            'mail.from.address' => 'hello@example.com',
            // Refused immediately: the command still prints its warnings, and no real mail is sent.
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $this->artisan('mail:test', ['email' => 'someone@example.com'])
            ->expectsOutputToContain('MAIL_USERNAME is empty')
            ->expectsOutputToContain('MAIL_PASSWORD is empty')
            ->expectsOutputToContain('MAIL_SCHEME must be "smtp" or "smtps"')
            ->expectsOutputToContain('@example.com placeholder')
            ->assertFailed();
    }

    public function test_a_correct_gmail_style_config_raises_no_warnings(): void
    {
        config([
            'mail.default' => 'array',
            'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.username' => 'school@gmail.com',
            'mail.mailers.smtp.password' => 'abcdefghijklmnop',
            'mail.mailers.smtp.scheme' => 'smtp',
            'mail.from.address' => 'school@gmail.com',
        ]);

        $this->artisan('mail:test', ['email' => 'someone@example.com'])
            ->doesntExpectOutputToContain('Config problem')
            ->assertSuccessful();
    }

    public function test_failed_reset_email_is_logged_and_the_user_sees_the_generic_message(): void
    {
        $user = User::factory()->create();
        Log::spy();

        // A transport that cannot connect: sending throws, but the page must not crash or reveal anything.
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1,
            'mail.mailers.smtp.timeout' => 1,
        ]);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status', fn ($m) => str_contains($m, "we've sent a password reset link"));

        Log::shouldHaveReceived('error')->withArgs(fn ($message) => $message === 'Password reset email failed to send.')->once();
    }

    public function test_env_example_documents_valid_gmail_settings(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        $this->assertStringContainsString('MAIL_HOST=smtp.gmail.com', $example);
        $this->assertStringContainsString('MAIL_SCHEME=smtp', $example);
        $this->assertStringNotContainsString('MAIL_SCHEME=tls', $example);
        $this->assertStringContainsString('app password', $example);
    }
}
