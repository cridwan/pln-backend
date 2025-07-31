<?php

use App\Models\Activity;
use App\Models\ConsMat;
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
        Schema::create('cons_mat_stds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Activity::class)->constrained()->onDelete('CASCADE');
            $table->foreignIdFor(ConsMat::class)->constrained()->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cons_mat_stds');
    }
};
