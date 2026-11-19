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

        $mailer = config('mail.default');

        if ($mailer === 'log') {
            $this->warn('MAIL_MAILER is "log": emails are only WRITTEN to storage/logs/laravel.log and will NOT be delivered.');
            $this->warn('Set MAIL_MAILER=smtp (plus host, username and password) in .env, run "php artisan config:clear", then try again.');
            $this->newLine();
        } elseif ($mailer === 'smtp') {
            $problems = [];
            foreach (['MAIL_HOST' => 'host', 'MAIL_USERNAME' => 'username', 'MAIL_PASSWORD' => 'password'] as $env => $key) {
                $value = config("mail.mailers.smtp.{$key}");
                if ($value === null || $value === '' || $value === 'null') {
                    $problems[] = "{$env} is empty";
                }
            }
            if (! in_array(config('mail.mailers.smtp.scheme'), [null, '', 'smtp', 'smtps'], true)) {
                $problems[] = 'MAIL_SCHEME must be "smtp" or "smtps" (not "'.config('mail.mailers.smtp.scheme').'")';
            }
            if (str_ends_with((string) config('mail.from.address'), '@example.com')) {
                $problems[] = 'MAIL_FROM_ADDRESS is still an @example.com placeholder';
            }
            foreach ($problems as $problem) {
                $this->warn('Config problem: '.$problem);
            }
            if ($problems !== []) {
                $this->newLine();
            }
        }

        try {
            Mail::raw('Koralink mail test at '.now()->toDateTimeString(), function ($message) use ($email) {
                $message->to($email)
                    ->subject('Koralink mail test');
            });

            $this->info("Test email accepted by mailer for {$email}.");
            $this->comment($mailer === 'log'
                ? 'Nothing was sent: it was only written to the log file.'
                : 'Check the inbox and the spam folder. If nothing arrives, check the mail provider\'s logs.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Mail send failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
