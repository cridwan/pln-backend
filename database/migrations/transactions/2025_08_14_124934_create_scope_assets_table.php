<?php

use App\Enums\ConnectionEnum;
use App\Models\Transaction\AdditionalScope;
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
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('scope_assets', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(ScopeStandart::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AdditionalScope::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->string('category');
            $table->string('color')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('scope_assets');
    }
};
