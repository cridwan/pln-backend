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
            $table->decimal('qty', 20, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpower_stds', function (Blueprint $table) {
            $table->dropColumn('qty');
        });
    }
};
