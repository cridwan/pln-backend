<?php

use App\Models\SubBidang;
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
        Schema::table('scope_standarts', function (Blueprint $table) {
            $table->foreign('inspection_type_uuid')->references('uuid')->on('inspection_types')->onDelete('CASCADE');
            $table->foreign('additional_scope_uuid')->references('uuid')->on('additional_scopes')->onDelete('CASCADE');
            $table->foreignIdFor(SubBidang::class)->nullable()->references('uuid')->on('sub_bidangs')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scope_standarts', function (Blueprint $table) {
            $table->dropColumn('sub_bidang_uuid');
            $table->dropForeign(['inspection_type_uuid', 'additional_scope_uuid']);
        });
    }
};
