<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundle_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('enrolled_at');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedSmallInteger('completed_courses')->default(0);
            $table->unsignedSmallInteger('total_courses')->default(0);
            $table->decimal('bundle_completion_percentage', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'bundle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_enrollments');
    }
};
