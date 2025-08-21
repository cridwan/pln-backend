<?php

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Models\SubBidang;
use App\Models\Transaction\AdditionalScope;
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
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('scope_standarts', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->text('name');
            $table->string('link')->nullable();
            $table->string('category');
            $table->foreignIdFor(SubBidang::class)->nullable()->constrained(DatabaseConnectionEnum::GLOBAL->value . '.sub_bidangs')->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AdditionalScope::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('scope_standarts');
    }
};
