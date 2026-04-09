<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('name')->constrained('countries')->nullOnDelete();
        });

        $defaultCountryId = DB::table('countries')->where('name', 'Germany')->value('id')
            ?: DB::table('countries')->orderBy('id')->value('id');

        if ($defaultCountryId) {
            DB::table('regions')->whereNull('country_id')->update(['country_id' => $defaultCountryId]);
        }

        Schema::table('regions', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }

    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('name')->constrained('organizations')->nullOnDelete();
        });

        $defaultOrganizationId = DB::table('organizations')->orderBy('id')->value('id');
        if ($defaultOrganizationId) {
            DB::table('regions')->whereNull('organization_id')->update(['organization_id' => $defaultOrganizationId]);
        }

        Schema::table('regions', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
