<?php

use App\Models\GlobalUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('name');
            $table->string('merk')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->foreignIdFor(GlobalUnit::class)->constrained()->cascadeOnDelete();
            $table->string('status')->default('general tools');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
