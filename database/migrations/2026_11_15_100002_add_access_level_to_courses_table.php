<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Every existing course stays open to everyone ("all").
            $table->string('access_level', 20)->default('all')->after('status');
            $table->foreignId('partner_organization_id')->nullable()->after('access_level')
                ->constrained('partner_organizations')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_organization_id');
            $table->dropColumn('access_level');
        });
    }
};
