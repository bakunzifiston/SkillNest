<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // NULL = unlimited attempts (how every existing quiz behaves today).
            $table->unsignedSmallInteger('max_attempts')->nullable()->after('time_limit_minutes');
            // true = a student may retake the quiz after passing it (today's behaviour).
            $table->boolean('allow_retake_after_pass')->default(true)->after('max_attempts');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            // true = the attempt was closed because the time limit ran out.
            $table->boolean('timed_out')->default(false)->after('passed');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('timed_out');
        });
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['max_attempts', 'allow_retake_after_pass']);
        });
    }
};
