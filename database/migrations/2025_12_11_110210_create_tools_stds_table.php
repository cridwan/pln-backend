<?php

use App\Models\Activity;
use App\Models\Tools;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tools_stds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Tools::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Activity::class)->constrained()->cascadeOnDelete();
            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools_stds');
    }
};
