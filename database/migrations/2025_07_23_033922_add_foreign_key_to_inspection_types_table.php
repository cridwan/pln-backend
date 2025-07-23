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
        Schema::table('inspection_types', function (Blueprint $table) {
            $table->foreign('machine_uuid')->references('uuid')->on('machines')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspection_types', function (Blueprint $table) {
            $table->dropForeign(['machine_uuid']);
        });
    }
};
