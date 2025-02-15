<?php

use App\Models\Transaction\Sequence;
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
        Schema::connection('transaction')->create('sequence_assets', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->text('note');
            $table->string('category');
            $table->string('color')->nullable();
            $table->foreignIdFor(Sequence::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->dropIfExists('sequence_assets');
    }
};
