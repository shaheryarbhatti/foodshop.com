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
        Schema::create('sidebar_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sidebar_module_id')->constrained()->onDelete('cascade');
            $table->string('title');      // e.g., "all_members"
            $table->string('route');      // e.g., "members.manage"
            $table->string('permission'); // The specific Spatie permission name
            $table->integer('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sidebar_options');
    }
};
