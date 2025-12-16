<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
        DELIMITER $$
            CREATE TRIGGER `DEL_ACTIVITY` BEFORE DELETE ON `activities` FOR EACH ROW BEGIN
                DELETE from cons_mat_stds WHERE cons_mat_stds.activity_uuid = OLD.uuid;
                DELETE FROM part_stds WHERE part_stds.activity_uuid = OLD.uuid;
                DELETE FROM manpower_stds WHERE manpower_stds.activity_uuid = OLD.uuid;
            END
            $$
        DELIMITER;");
        DB::unprepared("
        DELIMITER $$
            CREATE TRIGGER `DEL_ADD_SCOPE` BEFORE DELETE ON `additional_scopes` FOR EACH ROW BEGIN
                DELETE FROM scope_standarts where scope_standarts.additional_scope_uuid = OLD.uuid;
            END
            $$
        DELIMITER;");
        DB::unprepared("
        DELIMITER $$
            CREATE TRIGGER `DEL_EQUIPMENT` BEFORE DELETE ON `equipment` FOR EACH ROW BEGIN
                DELETE FROM activities WHERE activities.equipment_uuid = OLD.uuid;
            END
            $$
        DELIMITER;");
        DB::unprepared("
        DELIMITER $$
            CREATE TRIGGER `DEL_INSPECTION` BEFORE DELETE ON `inspection_types` FOR EACH ROW BEGIN
                DELETE FROM scope_standarts where scope_standarts.inspection_type_uuid = OLD.uuid;
                DELETE FROM additional_scopes WHERE additional_scopes.inspection_type_uuid = OLD.uuid;
            END
            $$
        DELIMITER;");
        DB::unprepared("
        DELIMITER $$
            CREATE TRIGGER `DEL_SCOPE_STANDAR` BEFORE DELETE ON `scope_standarts` FOR EACH ROW BEGIN
                DELETE FROM equipment WHERE equipment.scope_standart_uuid = OLD.uuid;
            END
            $$
        DELIMITER;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS DEL_ACTIVITY");
        DB::unprepared("DROP TRIGGER IF EXISTS DEL_ADD_SCOPE");
        DB::unprepared("DROP TRIGGER IF EXISTS DEL_EQUIPMENT");
        DB::unprepared("DROP TRIGGER IF EXISTS DEL_INSPECTION");
        DB::unprepared("DROP TRIGGER IF EXISTS DEL_SCOPE_STANDAR");
    }
};
