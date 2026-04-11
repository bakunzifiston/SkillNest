<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminUser extends Command
{
    protected $signature = 'admin:ensure {--email=admin@example.com : Admin email} {--password=password : Admin password}';

    protected $description = 'Create or update the super admin user so login works.';

    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Super Admin';
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->save();

        $this->info('Super admin user is ready.');
        $this->line('');
        $this->line('  Email:    ' . $email);
        $this->line('  Password: ' . $password);
        $this->line('');
        $this->line('Log in at: ' . url('/login'));

        return self::SUCCESS;
    }
}
