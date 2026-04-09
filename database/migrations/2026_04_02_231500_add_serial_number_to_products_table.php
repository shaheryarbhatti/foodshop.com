<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('serial_number')->nullable()->after('id');
        });

        $products = DB::table('products')->orderBy('id')->get(['id']);
        $serial = 1;
        foreach ($products as $product) {
            DB::table('products')->where('id', $product->id)->update(['serial_number' => $serial]);
            $serial++;
        }

        DB::statement('ALTER TABLE products MODIFY serial_number INT UNSIGNED NOT NULL');

        Schema::table('products', function (Blueprint $table) {
            $table->unique('serial_number');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['serial_number']);
            $table->dropColumn('serial_number');
        });
    }
};
