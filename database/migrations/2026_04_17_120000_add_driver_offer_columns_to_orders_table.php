<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'driver_offer_status')) {
                $table->string('driver_offer_status', 30)->nullable()->after('driver_id');
            }

            if (! Schema::hasColumn('orders', 'driver_offer_driver_id')) {
                $table->foreignId('driver_offer_driver_id')->nullable()->after('driver_offer_status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('orders', 'driver_offer_attempts')) {
                $table->unsignedInteger('driver_offer_attempts')->default(0)->after('driver_offer_driver_id');
            }

            if (! Schema::hasColumn('orders', 'driver_offer_sent_at')) {
                $table->timestamp('driver_offer_sent_at')->nullable()->after('driver_offer_attempts');
            }

            if (! Schema::hasColumn('orders', 'driver_offer_responded_at')) {
                $table->timestamp('driver_offer_responded_at')->nullable()->after('driver_offer_sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'driver_offer_driver_id')) {
                $table->dropConstrainedForeignId('driver_offer_driver_id');
            }

            foreach ([
                'driver_offer_status',
                'driver_offer_attempts',
                'driver_offer_sent_at',
                'driver_offer_responded_at',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
