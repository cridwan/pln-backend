<?php

use App\Models\GlobalUnit;
use App\Models\Transaction\AdditionalScope;
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
        Schema::connection('transaction')->create('additional_const_mats', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('merk')->nullable();
            $table->integer('qty');
            $table->foreignIdFor(GlobalUnit::class);
            $table->foreignIdFor(AdditionalScope::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->dropIfExists('additional_const_mats');
    }
};
