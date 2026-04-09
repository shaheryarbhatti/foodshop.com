<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_fees', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('fee', 10, 2)->default(0);
            $table->enum('condition_type', ['less_than', 'equal_to', 'greater_than', 'between'])->default('less_than');
            $table->string('distance_value');
            $table->decimal('maximum_order_amount', 10, 2)->default(0);
            $table->enum('delivery_type', ['delivery', 'pickup'])->default('delivery');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_fees');
    }
};
