<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // true = a lesson unlocks when the previous lesson is completed. An admin can switch it off per course.
            $table->boolean('sequential_lessons')->default(true)->after('access_level');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('sequential_lessons');
        });
    }
};
