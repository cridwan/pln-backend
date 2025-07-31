<?php

use App\Models\AdditionalScope;
use App\Models\InspectionType;
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
            $table->dropForeign(['inspection_type_uuid']);
            $table->dropColumn('inspection_type_uuid');
            $table->dropForeign(['additional_scope_uuid']);
            $table->dropColumn('additional_scope_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sequences', function (Blueprint $table) {
            $table->foreignIdFor(InspectionType::class)->constrained()->onDelete('CASCADE');
            $table->foreignIdFor(AdditionalScope::class)->constrained()->onDelete('CASCADE');
        });
    }
};
