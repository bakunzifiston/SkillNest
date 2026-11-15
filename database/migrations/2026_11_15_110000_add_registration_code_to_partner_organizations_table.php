<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->string('registration_code', 32)->nullable()->after('slug');
        });

        // Give every existing organization (e.g. SFH) a code. Share it with that
        // organization's people; it is shown on the admin "Partner organizations" page.
        foreach (DB::table('partner_organizations')->whereNull('registration_code')->pluck('id') as $id) {
            DB::table('partner_organizations')->where('id', $id)->update([
                'registration_code' => strtoupper(Str::random(8)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('partner_organizations', function (Blueprint $table) {
            $table->dropColumn('registration_code');
        });
    }
};
