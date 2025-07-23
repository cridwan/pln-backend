<?php

use App\Models\HseDoc;
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
        Schema::table('hses', function (Blueprint $table) {
            $table->foreignIdFor(HseDoc::class)->nullable()->references('uuid')->on('hse_docs')->onDelete('CASCADE');
            $table->foreign('inspection_type_uuid')->references('uuid')->on('inspection_types')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hses', function (Blueprint $table) {
            $table->dropForeign(['inspection_type_uuid', 'hse_doc_uuid']);
        });
    }
};
