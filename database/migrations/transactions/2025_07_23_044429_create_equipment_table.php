<?php

use App\Enums\ConnectionEnum;
use App\Models\Transaction\ScopeStandart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('equipment', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(ScopeStandart::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('link_ik1')->nullable();
            $table->string('link_ik2')->nullable();
            $table->uuid('original_uuid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('equipment');
    }
};
