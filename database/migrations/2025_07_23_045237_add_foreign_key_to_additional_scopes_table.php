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
        Schema::table('additional_scopes', function (Blueprint $table) {
            $table->foreign('inspection_type_uuid')->references('uuid')->on('inspection_types')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_scopes', function (Blueprint $table) {
            $table->dropForeign(['inspection_type_uuid']);
        });
    }
};
