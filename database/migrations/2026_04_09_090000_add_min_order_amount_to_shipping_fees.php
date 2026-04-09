<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_fees', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->default(0)->after('fee');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_fees', function (Blueprint $table) {
            $table->dropColumn('min_order_amount');
        });
    }
};
