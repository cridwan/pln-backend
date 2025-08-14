<?php

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Models\InspectionType;
use App\Models\Sequence;
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
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('projects', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->foreignIdFor(InspectionType::class)->nullable()->constrained(DatabaseConnectionEnum::GLOBAL->value . '.inspection_types')->cascadeOnDelete();
            $table->foreignIdFor(Sequence::class)->nullable()->constrained(DatabaseConnectionEnum::GLOBAL->value . '.sequences')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('projects');
    }
};
