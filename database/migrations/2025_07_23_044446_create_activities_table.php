<?php

use App\Models\Equipment;
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
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Equipment::class)->references('uuid')->on('equipment')->onDelete('CASCADE');
            $table->string('name');
            $table->integer('duration')->default(0);
            $table->string('link_ik1')->nullable();
            $table->string('link_ik2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
