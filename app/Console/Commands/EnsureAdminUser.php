<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class EnsureAdminUser extends Command
{
    protected $signature = 'admin:ensure {--email= : Admin email} {--password= : Admin password} {--name= : Admin name}';

    protected $description = 'Create or update the super admin user so login works.';

    public function handle(): int
    {
        $this->ensureRolesTables();

        $email = $this->option('email') ?: config('admin.email');
        $password = $this->option('password') ?: config('admin.password');
        $name = $this->option('name') ?: config('admin.name');

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->is_active = true;
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->assignRole(Role::ensureSystemRoles());
        $user->save();

        $this->info('Super admin user is ready.');
        $this->line('');
        $this->line('  Email:    '.$email);
        $this->line('  Password: '.$password);
        $this->line('');
        $this->line('Log in at: '.url('/login'));

        return self::SUCCESS;
    }

    private function ensureRolesTables(): void
    {
        if (Schema::hasTable('roles') && Schema::hasColumn('users', 'is_active')) {
            return;
        }

        $migration = '2026_09_18_132200_create_roles_and_permissions_tables';

        DB::table('migrations')->where('migration', $migration)->delete();

        $this->call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/'.$migration.'.php',
        ]);
    }
}
