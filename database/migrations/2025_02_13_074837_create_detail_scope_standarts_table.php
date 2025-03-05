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
        Schema::create('detail_scope_standarts', function (Blueprint $table) {
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
        Schema::dropIfExists('detail_scope_standarts');
    }
};
