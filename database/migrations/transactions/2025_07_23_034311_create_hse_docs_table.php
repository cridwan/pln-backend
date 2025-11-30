<?php

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Models\HseDoc;
use App\Models\Transaction\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('hse_docs', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('link')->nullable();
            $table->uuid('original_uuid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('hse_docs');
    }
};
