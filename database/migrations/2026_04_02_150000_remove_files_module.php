<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sidebar_options')->where('route', 'files.manage')->delete();
        DB::table('permissions')->whereIn('name', ['files.manage', 'files.view', 'files.add', 'files.edit', 'files.delete'])->delete();
        Schema::dropIfExists('files');
    }

    public function down(): void
    {
    }
};
