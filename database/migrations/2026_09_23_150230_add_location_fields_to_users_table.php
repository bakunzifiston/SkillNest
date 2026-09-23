<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country', 100)->nullable()->after('email');
            $table->string('province', 100)->nullable()->after('country');
            $table->string('district', 100)->nullable()->after('province');
            $table->string('sector', 100)->nullable()->after('district');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'province', 'district', 'sector']);
        });
    }
};
