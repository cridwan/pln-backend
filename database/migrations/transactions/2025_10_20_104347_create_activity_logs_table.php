<?php

use App\Enums\ConnectionEnum;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->create('activity_logs', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('activity_type');
            $table->string('activity_id');
            $table->foreignIdFor(User::class, 'created_id')->nullable();
            $table->foreignIdFor(User::class, 'updated_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection(ConnectionEnum::TRANSACTION->value)->dropIfExists('activity_logs');
    }
};
