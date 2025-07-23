<?php

use App\Models\ScopeStandart;
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
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(ScopeStandart::class)->references('uuid')->on('scope_standarts')->onDelete('CASCADE');
            $table->string('name');
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
        Schema::dropIfExists('equipment');
    }
};
