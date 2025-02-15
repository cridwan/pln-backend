<?php

use App\Models\Area;
use App\Models\InspectionType;
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
        Schema::create('additional_scopes', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('link')->nullable();
            $table->foreignIdFor(InspectionType::class);
            $table->foreignIdFor(Area::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_scopes');
    }
};
