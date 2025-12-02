<?php

namespace App\Core\Transaction\Detail;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\Manpower;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ManpowerStdCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'manpower.globalUnit'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'manpower.name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Manpower::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Manpower::query()
            ->has('activity.equipment.scopeStandart.additionalScope');
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            'qty' => ['required', 'numeric']
        ];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function attributeExport(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function attributeTemplate(): TemplateData
    {
        return new TemplateData([], []);
    }

    #[DoNotDiscover]
    public function mapping($data, $index)
    {
    }

    #[DoNotDiscover]
    public function validateData($data, $index): array
    {
        return [];
    }
}
