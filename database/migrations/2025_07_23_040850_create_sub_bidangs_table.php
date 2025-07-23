<?php

use App\Models\Bidang;
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
        Schema::create('sub_bidangs', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->foreignIdFor(Bidang::class)->references('uuid')->on('bidangs')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_bidangs');
    }
};
