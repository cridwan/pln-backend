<?php

use App\Models\Activity;
use App\Models\Manpower;
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
        Schema::create('manpower_stds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Activity::class)->constrained()->onDelete('CASCADE');
            $table->foreignIdFor(Manpower::class)->constrained()->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpower_stds');
    }
};
