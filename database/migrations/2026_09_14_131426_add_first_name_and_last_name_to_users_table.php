<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $full = trim((string) $user->name);
                if ($full === '') {
                    continue;
                }

                $parts = preg_split('/\s+/', $full, 2);
                $first = $parts[0] ?? '';
                $last = $parts[1] ?? '';

                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $first !== '' ? $first : null,
                    'last_name' => $last !== '' ? $last : null,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
