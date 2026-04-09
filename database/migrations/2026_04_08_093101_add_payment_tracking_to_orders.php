<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('grand_total');
        });

        // Initialize amount_paid for existing paid orders
        \App\Models\Order::where('payment_status', 'paid')
            ->each(function ($order) {
                $order->update(['amount_paid' => $order->grand_total]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('amount_paid');
        });
    }
};
