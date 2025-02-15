<?php

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
        Schema::connection('transaction')->create('additional_manpowers', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->integer('qty');
            $table->string('type');
            $table->text('note');
            $table->foreignIdFor(AdditionalScope::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->dropIfExists('additional_manpowers');
    }
};
