<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email : Address to send a test message to}';

    protected $description = 'Send a test email using the current mail configuration';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $this->info('Mailer: '.config('mail.default'));
        $this->info('Host: '.config('mail.mailers.smtp.host'));
        $this->info('Port: '.config('mail.mailers.smtp.port'));
        $this->info('Scheme: '.(config('mail.mailers.smtp.scheme') ?: '(none)'));
        $this->info('From: '.config('mail.from.address'));
        $this->info('APP_URL: '.config('app.url'));
        $this->newLine();

        try {
            Mail::raw('SkillNest mail test at '.now()->toDateTimeString(), function ($message) use ($email) {
                $message->to($email)
                    ->subject('SkillNest mail test');
            });

            $this->info("Test email accepted by mailer for {$email}.");
            $this->comment('Check inbox and spam. If nothing arrives, check server mail logs / Exim.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Mail send failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
