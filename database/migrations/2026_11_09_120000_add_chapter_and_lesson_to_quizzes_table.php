<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('chapter_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            $table->foreignId('lesson_id')->nullable()->after('chapter_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lesson_id');
            $table->dropConstrainedForeignId('chapter_id');
        });
    }
};
