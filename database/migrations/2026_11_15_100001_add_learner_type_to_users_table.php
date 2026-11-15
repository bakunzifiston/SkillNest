<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Every existing account becomes an Agent, exactly as it behaves today.
            $table->string('learner_type', 20)->default('agent')->after('sector');
            $table->foreignId('partner_organization_id')->nullable()->after('learner_type')
                ->constrained('partner_organizations')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('partner_organization_id');
            $table->dropColumn('learner_type');
        });
    }
};
