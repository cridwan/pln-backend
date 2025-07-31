<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('const_mats', function (Blueprint $table) {
            $table->dropColumn('qty');
            $table->dropForeign(['activity_uuid']);
            $table->dropColumn('activity_uuid');
            $table->decimal('price', 20, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('const_mats', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->integer('qty');
            $table->foreignIdFor(Activity::class)->nullable()->references('uuid')->on('activities')->onDelete('CASCADE');
        });
    }
};
