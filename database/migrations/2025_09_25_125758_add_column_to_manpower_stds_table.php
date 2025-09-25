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
        Schema::table('manpower_stds', function (Blueprint $table) {
            $table->unique(['manpower_uuid', 'activity_uuid']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpower_stds', function (Blueprint $table) {
            $table->dropUnique(['manpower_uuid', 'activity_uuid']);
        });
    }
};
