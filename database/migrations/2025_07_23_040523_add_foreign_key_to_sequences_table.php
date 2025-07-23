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
        Schema::table('sequences', function (Blueprint $table) {
            $table->foreign('inspection_type_uuid')->references('uuid')->on('inspection_types')->onDelete('CASCADE');
            $table->foreign('additional_scope_uuid')->references('uuid')->on('additional_scopes')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sequences', function (Blueprint $table) {
            $table->dropForeign(['inspection_type_uuid', 'additional_scope_uuid']);
        });
    }
};
