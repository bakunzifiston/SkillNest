<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->string('action');
            $table->timestamps();
            $table->unique(['role_id', 'module', 'action']);
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->string('action');
            $table->timestamps();
            $table->unique(['user_id', 'module', 'action']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('is_admin')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('role_id');
        });

        $now = now();
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'description' => 'Full access to every admin module.',
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach (config('admin-modules.modules', []) as $module => $definition) {
            foreach ($definition['actions'] ?? [] as $action) {
                DB::table('role_permissions')->insert([
                    'role_id' => $roleId,
                    'module' => $module,
                    'action' => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('users')->where('is_admin', true)->update([
            'role_id' => $roleId,
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
