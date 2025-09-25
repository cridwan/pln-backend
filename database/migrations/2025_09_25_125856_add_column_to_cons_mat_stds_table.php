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
        Schema::table('cons_mat_stds', function (Blueprint $table) {
            $table->unique(['cons_mat_uuid', 'activity_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cons_mat_stds', function (Blueprint $table) {
            $table->dropUnique(['cons_mat_uuid', 'activity_uuid']);
        });
    }
};
