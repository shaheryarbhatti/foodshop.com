<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_gateway')->nullable()->after('payment_method');
            $table->string('payment_status')->nullable()->after('payment_gateway');
            $table->string('payment_reference')->nullable()->after('payment_status');
            $table->string('payment_currency', 10)->nullable()->after('payment_reference');
            $table->json('payment_payload')->nullable()->after('payment_currency');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_gateway',
                'payment_status',
                'payment_reference',
                'payment_currency',
                'payment_payload',
            ]);
        });
    }
};
