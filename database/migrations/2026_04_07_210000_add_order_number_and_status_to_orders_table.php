<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_number')->nullable()->unique()->after('id');
            $table->string('order_status')->default(Order::STATUS_PENDING_PAYMENT)->after('payment_status');
        });

        DB::table('orders')
            ->orderBy('id')
            ->select(['id', 'created_at', 'payment_method', 'payment_status'])
            ->get()
            ->each(function ($order) {
                $datePart = $order->created_at
                    ? \Illuminate\Support\Carbon::parse($order->created_at)->format('Ymd')
                    : now()->format('Ymd');

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'order_number' => 'ORD-' . $datePart . '-' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                        'order_status' => Order::resolveInitialStatus($order->payment_method, $order->payment_status),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['order_number']);
            $table->dropColumn(['order_number', 'order_status']);
        });
    }
};
