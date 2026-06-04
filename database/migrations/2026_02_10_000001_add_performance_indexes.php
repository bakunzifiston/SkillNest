<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add performance indexes to foreign key columns and commonly queried fields.
     */
    public function up(): void
    {
        // Enrollments
        if (Schema::hasTable('enrollments')) {
            Schema::table('enrollments', function (Blueprint $table) {
                $table->index('course_id');
                $table->index('created_at');
            });
        }

        // Lesson completions
        if (Schema::hasTable('lesson_completions')) {
            Schema::table('lesson_completions', function (Blueprint $table) {
                $table->index('lesson_id');
                $table->index('completed_at');
            });
        }

        // Reviews
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->index('course_id');
                $table->index('created_at');
            });
        }

        // Quiz attempts
        if (Schema::hasTable('quiz_attempts')) {
            Schema::table('quiz_attempts', function (Blueprint $table) {
                $table->index('quiz_id');
                $table->index('passed');
                $table->index('submitted_at');
            });
        }

        // Quiz attempt answers
        if (Schema::hasTable('quiz_attempt_answers')) {
            Schema::table('quiz_attempt_answers', function (Blueprint $table) {
                $table->index('quiz_attempt_id');
                $table->index('question_id');
            });
        }

        // Bundle enrollments
        if (Schema::hasTable('bundle_enrollments')) {
            Schema::table('bundle_enrollments', function (Blueprint $table) {
                $table->index('bundle_id');
                $table->index('enrolled_at');
                $table->index('completed_at');
            });
        }

        // Bundle courses
        if (Schema::hasTable('bundle_courses')) {
            Schema::table('bundle_courses', function (Blueprint $table) {
                $table->index('course_id');
                $table->index('order');
            });
        }

        // Live sessions
        if (Schema::hasTable('live_sessions')) {
            Schema::table('live_sessions', function (Blueprint $table) {
                $table->index('course_id');
                $table->index('scheduled_at');
            });
        }

        // Live session users
        if (Schema::hasTable('live_session_user')) {
            Schema::table('live_session_user', function (Blueprint $table) {
                $table->index('live_session_id');
                $table->index('user_id');
            });
        }

        // Courses
        if (Schema::hasTable('courses')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->index('instructor_id');
                $table->index('slug');
            });
        }

        // Chapters
        if (Schema::hasTable('chapters')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('sort_order');
            });
        }

        // Lessons
        if (Schema::hasTable('lessons')) {
            Schema::table('lessons', function (Blueprint $table) {
                $table->index('type');
                $table->index('sort_order');
            });
        }

        // Questions
        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->index('type');
                $table->index('sort_order');
            });
        }

        // Question options
        if (Schema::hasTable('question_options')) {
            Schema::table('question_options', function (Blueprint $table) {
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        // Note: This migration adds indexes. Rollback will drop the entire table on down(),
        // which is already handled by the original migrations.
    }
};
