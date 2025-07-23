<?php

use App\Models\Activity;
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
        Schema::table('manpowers', function (Blueprint $table) {
            $table->dropColumn('inspection_type_uuid');
            $table->dropColumn('additional_scope_uuid');
            $table->decimal('price', 20, 2)->default(0);
            $table->foreignIdFor(Activity::class)->nullable()->references('uuid')->on('activities')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpowers', function (Blueprint $table) {
            $table->foreignIdFor(InspectionType::class);
            $table->foreignIdFor(AdditionalScope::class);
            $table->dropColumn('price');
            $table->dropColumn(['activity_uuid']);
        });
    }
};
