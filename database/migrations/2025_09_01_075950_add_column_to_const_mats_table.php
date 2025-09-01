<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('const_mats', function (Blueprint $table) {
            $table->string('name')->change();
            $table->unique(['name', 'merk', 'global_unit_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('const_mats', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropUnique(['name', 'merk', 'global_unit_uuid']);
        });
    }
};
