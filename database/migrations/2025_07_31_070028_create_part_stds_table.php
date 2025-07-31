<?php

use App\Models\Activity;
use App\Models\Part;
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
        Schema::create('part_stds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Activity::class)->constrained()->onDelete('CASCADE');
            $table->foreignIdFor(Part::class)->constrained()->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('part_stds');
    }
};
