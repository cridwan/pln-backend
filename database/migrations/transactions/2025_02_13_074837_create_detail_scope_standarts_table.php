<?php

use App\Models\Transaction\ScopeStandart;
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
        Schema::connection('transaction')->create('detail_scope_standarts', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->text('name');
            $table->string('link')->nullable();
            $table->foreignIdFor(ScopeStandart::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->dropIfExists('detail_scope_standarts');
    }
};
