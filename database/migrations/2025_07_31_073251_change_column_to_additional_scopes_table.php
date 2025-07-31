<?php

use App\Models\Sequence;
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
        Schema::table('additional_scopes', function (Blueprint $table) {
            $table->foreignIdFor(Sequence::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additional_scopes', function (Blueprint $table) {
            $table->dropForeign(['sequence_uuid']);
            $table->dropColumn('sequence_uuid');
        });
    }
};
