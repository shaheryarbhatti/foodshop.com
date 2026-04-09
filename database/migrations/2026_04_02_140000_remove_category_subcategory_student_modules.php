<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('sidebar_options')->whereIn('route', ['categories.manage', 'subcategories.manage', 'students.manage'])->delete();
        DB::table('sidebar_modules')->where('permission', 'students')->delete();
        DB::table('permissions')->whereIn('name', ['categories.manage','categories.view','categories.add','categories.edit','categories.delete','subcategories.manage','subcategories.view','subcategories.add','subcategories.edit','subcategories.delete','students.manage','students.view','students.add','students.edit','students.delete','students'])->delete();
        DB::table('settings')->where('key', 'student_unique_id_start')->delete();
        Schema::dropIfExists('file_sub_category');
        Schema::dropIfExists('category_location');
        Schema::dropIfExists('sub_categories');
        Schema::dropIfExists('students');
        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
    }
};
