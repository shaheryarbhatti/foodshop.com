<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_addon_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_addon_id')->constrained('product_addons')->cascadeOnDelete();
            $table->string('title');
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_addon_values');
    }
};
