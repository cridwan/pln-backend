<?php

use App\Enums\HseTypeEnum;
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
        Schema::connection('transaction')->table('hses', function (Blueprint $table) {
            $table->string('type')->default(HseTypeEnum::STANDART->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('transaction')->table('hses', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
