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
            $table->dropColumn('link');
            $table->dropColumn('day');
            $table->dropColumn('category');
            $table->dropColumn('animation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_scopes', function (Blueprint $table) {
            $table->string('category');
            $table->integer('day');
            $table->string('animation');
            $table->string('link');
        });
    }
};
