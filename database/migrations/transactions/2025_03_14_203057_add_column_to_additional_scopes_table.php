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
        Schema::connection('transaction')->table('additional_scopes', function (Blueprint $table) {
            $table->string('animation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->table('additional_scopes', function (Blueprint $table) {
            $table->dropColumn('animation');
        });
    }
};
