<?php

use App\Models\AdditionalScope;
use App\Models\GlobalUnit;
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
        Schema::create('additional_parts', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->integer('qty');
            $table->string('no_drawing');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('additional_parts');
    }
};
