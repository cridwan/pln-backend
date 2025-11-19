<?php

use App\Models\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropForeign(['activity_uuid']);
            $table->dropColumn('activity_uuid');
            $table->dropColumn('qty');
            $table->decimal('price', 20, 2)->default(0);
            $table->string('merk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->foreignIdFor(Activity::class)->constrained('activities')->onDelete('CASCADE');
            $table->integer('qty');
            $table->string('size');
            $table->string('location');
        });
    }
};
