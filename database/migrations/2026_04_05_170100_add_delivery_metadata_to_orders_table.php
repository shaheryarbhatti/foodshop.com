<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('customer_id')->constrained('organizations')->nullOnDelete();
            $table->foreignId('shipping_fee_id')->nullable()->after('organization_id')->constrained('shipping_fees')->nullOnDelete();
            $table->decimal('delivery_distance_km', 10, 2)->nullable()->after('shipping_fee_id');
            $table->decimal('customer_latitude', 10, 7)->nullable()->after('delivery_distance_km');
            $table->decimal('customer_longitude', 10, 7)->nullable()->after('customer_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_id');
            $table->dropConstrainedForeignId('shipping_fee_id');
            $table->dropColumn(['delivery_distance_km', 'customer_latitude', 'customer_longitude']);
        });
    }
};
