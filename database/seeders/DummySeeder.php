<?php

namespace Database\Seeders;

use App\Enums\ManpowerTypeEnum;
use App\Enums\ScopeStandartCategoryEnum;
use App\Enums\ToolSectionEnum;
use App\Models\AdditionalScope;
use App\Models\ConstMat;
use App\Models\DetailScopeStandart;
use App\Models\GlobalUnit;
use App\Models\Hse;
use App\Models\InspectionType;
use App\Models\Location;
use App\Models\Machine;
use App\Models\Manpower;
use App\Models\Part;
use App\Models\ScopeStandart;
use App\Models\Sequence;
use App\Models\SequenceAnimation;
use App\Models\Tools;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $location = Location::create([
                'name' => 'Priok',
                'slug' => 'priok',
                'lat' => '-6.139100',
                'lon' => '106.866802',
                'color' => '#D84040',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.'
            ]);

            $unit = Unit::create([
                'name' => 'Blok 1/2',
                'location_uuid' => $location->uuid
            ]);

            $machine = Machine::create([
                'name' => 'GT 1.3 ABB 13E1',
                'unit_uuid' => $unit->uuid
            ]);

            $inspectionType = InspectionType::create([
                'name' => 'Turbin Inspection',
                'machine_uuid' => $machine->uuid
            ]);

            InspectionType::create([
                'name' => 'Combustion Inspection',
                'machine_uuid' => $machine->uuid
            ]);

            InspectionType::create([
                'name' => 'Major Inspection',
                'machine_uuid' => $machine->uuid
            ]);

            $globalUnit = GlobalUnit::create([
                'name' => 'Pcs'
            ]);

            Sequence::create([
                'name' => 'Manhon Turbine Sylinder',
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            $scopeStandart = ScopeStandart::create([
                'name' => 'Enclosure',
                'category' => ScopeStandartCategoryEnum::LISTRIK->value,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            DetailScopeStandart::create([
                'name' => 'Pelepasan Enclosure dan pengecekan secara visual kondisinya, koordinasikan dengan K3 untuk bagian pemadam',
                'scope_standart_uuid' => $scopeStandart->uuid
            ]);

            DetailScopeStandart::create([
                'name' => 'Pemasangan Enclosure',
                'scope_standart_uuid' => $scopeStandart->uuid
            ]);

            ConstMat::create([
                'name' => 'WD-40',
                'merk' => 'Toyota',
                'qty' => 10,
                'global_unit_uuid' => $globalUnit->uuid,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            Manpower::create([
                'name' => 'Site Coordinator',
                'qty' => 10,
                'type' => ManpowerTypeEnum::PEOPLE->value,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            Part::create([
                'name' => 'Transition Piece',
                'qty' => 10,
                'no_drawing' => '2025/01/023',
                'global_unit_uuid' => $globalUnit->uuid,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            Hse::create([
                'title' => 'Dokumen Kesepakatan OH',
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            $additionalScope = AdditionalScope::create([
                'name' => 'Turbine Blade Row 1',
                'category' => ScopeStandartCategoryEnum::LISTRIK->value,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            $detailScopeAdditonalStandart = ScopeStandart::create([
                'name' => 'GT Casing',
                'category' => ScopeStandartCategoryEnum::MEKANIK->value,
                'additional_scope_uuid' => $additionalScope->uuid
            ]);

            DetailScopeStandart::create([
                'name' => 'Pelepasan Enclosure dan pengecekan secara visual kondisinya, koordinasikan dengan K3 untuk bagian pemadam',
                'scope_standart_uuid' => $detailScopeAdditonalStandart->uuid
            ]);

            DetailScopeStandart::create([
                'name' => 'Blade Ring Row 1',
                'scope_standart_uuid' => $detailScopeAdditonalStandart->uuid
            ]);

            ConstMat::create([
                'name' => 'WD-40',
                'merk' => 'Toyota',
                'qty' => 10,
                'global_unit_uuid' => $globalUnit->uuid,
                'additional_scope_uuid' => $additionalScope->uuid
            ]);

            Manpower::create([
                'name' => 'Site Coordinator',
                'qty' => 10,
                'type' => ManpowerTypeEnum::PEOPLE->value,
                'additional_scope_uuid' => $additionalScope->uuid
            ]);

            Part::create([
                'name' => 'Transition Piece',
                'qty' => 10,
                'no_drawing' => '2025/01/023',
                'global_unit_uuid' => $globalUnit->uuid,
                'additional_scope_uuid' => $additionalScope->uuid
            ]);

            Tools::create([
                'name' => 'Palu',
                'qty' => 1,
                'global_unit_uuid' => $globalUnit->uuid,
                'section' => ToolSectionEnum::MECHANICAL->value,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            Tools::create([
                'name' => 'Obeng',
                'qty' => 1,
                'global_unit_uuid' => $globalUnit->uuid,
                'section' => ToolSectionEnum::ELECTRICAL->value,
                'inspection_type_uuid' => $inspectionType->uuid
            ]);

            SequenceAnimation::create([
                'name' => 'Exhaust Section',
                'slug' => 'exhaust-section'
            ]);

            SequenceAnimation::create([
                'name' => 'Turbin Section',
                'slug' => 'turbine-section'
            ]);

            SequenceAnimation::create([
                'name' => 'Combustion Section',
                'slug' => 'combustion-section'
            ]);

            SequenceAnimation::create([
                'name' => 'Compressor Section',
                'slug' => 'compressor-section'
            ]);

            SequenceAnimation::create([
                'name' => 'Inlet Section',
                'slug' => 'inlet-section'
            ]);

            SequenceAnimation::create([
                'name' => 'Generator Section',
                'slug' => 'generator-section'
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
