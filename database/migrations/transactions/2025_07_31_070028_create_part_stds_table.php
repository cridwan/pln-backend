<?php

use App\Enums\ConnectionEnum;
use App\Enums\DatabaseConnectionEnum;
use App\Models\Part;
use App\Models\Transaction\Activity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('part_stds', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->foreignIdFor(Activity::class)->constrained()->onDelete('CASCADE');
            $table->string('name');
            $table->string('merk');
            $table->string('no_drawing');
            $table->string('unit');
            $table->decimal('price', 20, 2);
            $table->decimal('qty', 20, 2)->default(0);
            $table->uuid('original_uuid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('part_stds');
    }
};
