<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\AdditionalScope;
use App\Models\Equipment;
use App\Models\ScopeStandart;
use App\Models\SubBidang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $additionalScope = AdditionalScope::find('4cce7770-46d4-4c63-a553-194f5644d603');
            $subBidang = SubBidang::latest()->first();
            $scopeStandart = ScopeStandart::create([
                'name' => 'TESTING ADDITIONAL SCOPE',
                'category' => 'mekanik',
                'additional_scope_uuid' => $additionalScope->uuid,
                'sub_bidang_uuid' => $subBidang->uuid,
            ]);

            $equipment = Equipment::create([
                'name' => 'EQUIPMENT ADDITIONAL',
                'scope_standart_uuid' => $scopeStandart->uuid,
            ]);

            Activity::create([
                'name' => 'ACTIVITY ADDITIONAL',
                'duration' => 10,
                'equipment_uuid' => $equipment->uuid,
            ]);
        });
    }
}
