<?php

use App\Models\Activity;
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
            $table->dropForeign(['activity_uuid']);
            $table->dropColumn('activity_uuid');
            $table->dropColumn('qty');
            $table->dropColumn('type');
            $table->dropColumn('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manpowers', function (Blueprint $table) {
            $table->foreignIdFor(Activity::class)->constrained('activities')->onDelete('CASCADE');
            $table->decimal('qty');
            $table->string('type');
            $table->string('note');
        });
    }
};
