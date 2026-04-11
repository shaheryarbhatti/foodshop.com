<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('allergies') || ! Schema::hasColumn('allergies', 'code')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `allergies` DROP INDEX `allergies_code_unique`');
            DB::statement('ALTER TABLE `allergies` MODIFY `code` VARCHAR(191) NOT NULL');
            DB::statement('ALTER TABLE `allergies` ADD UNIQUE KEY `allergies_code_unique` (`code`)');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('allergies') || ! Schema::hasColumn('allergies', 'code')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE `allergies` DROP INDEX `allergies_code_unique`');
            DB::statement('ALTER TABLE `allergies` MODIFY `code` VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE `allergies` ADD UNIQUE KEY `allergies_code_unique` (`code`)');
        }
    }
};
