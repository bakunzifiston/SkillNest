<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Optional KYC details for Agents. All nullable: existing users are unaffected.
            $table->string('phone', 30)->nullable()->after('email');
            $table->text('national_id')->nullable()->after('phone'); // stored encrypted
            $table->date('date_of_birth')->nullable()->after('national_id');
            $table->string('gender', 20)->nullable()->after('date_of_birth');
            $table->string('occupation', 150)->nullable()->after('gender');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'national_id', 'date_of_birth', 'gender', 'occupation']);
        });
    }
};
