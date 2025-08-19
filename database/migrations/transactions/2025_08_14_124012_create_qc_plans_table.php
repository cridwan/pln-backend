<?php

use App\Enums\ConnectionEnum;
use App\Models\Transaction\Project;
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
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('qc_plans', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('qc_mekanik')->nullable();
            $table->string('qc_listrik')->nullable();
            $table->string('qc_instrument')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('qc_plans');
    }
};
