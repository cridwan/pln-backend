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
        Schema::table('tools_stds', function (Blueprint $table) {
            $table->unique(['tools_uuid', 'activity_uuid'], 'unique_tools_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tools_stds', function (Blueprint $table) {
            $table->dropUnique('unique_tools_activity');
        });
    }
};
