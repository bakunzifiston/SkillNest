<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Content tables whose public text can be translated. The original (English) columns stay as they are. */
    private array $tables = [
        'categories', 'courses', 'chapters', 'lessons', 'quizzes',
        'questions', 'question_options', 'bundles', 'instructors', 'live_sessions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                // {"rw": {"title": "...", "description": "..."}} - missing keys fall back to English.
                $t->json('translations')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn('translations');
            });
        }
    }
};
