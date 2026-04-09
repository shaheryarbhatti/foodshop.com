<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum must include every allowed value, otherwise writes get truncated.
        DB::statement(
            "ALTER TABLE `product_addons` MODIFY `selection_type` ENUM('single','multiple','listing') NOT NULL DEFAULT 'single'"
        );
    }

    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `product_addons` MODIFY `selection_type` ENUM('single','multiple') NOT NULL DEFAULT 'single'"
        );
    }
};

