<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create the pivot table
        Schema::create('product_addon_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_addon_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Migrate existing data from product_addons.product_id to product_addon_product
        $addons = DB::table('product_addons')->whereNotNull('product_id')->get();
        foreach ($addons as $addon) {
            DB::table('product_addon_product')->insert([
                'product_id' => $addon->product_id,
                'product_addon_id' => $addon->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Make product_id nullable in product_addons (preparation for removal)
        Schema::table('product_addons', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_addon_product');

        Schema::table('product_addons', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
