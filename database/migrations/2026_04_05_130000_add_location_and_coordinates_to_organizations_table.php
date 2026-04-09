<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('name')->constrained('locations')->nullOnDelete();
            $table->string('address')->nullable()->after('location_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id', 'address', 'latitude', 'longitude']);
        });
    }
};
