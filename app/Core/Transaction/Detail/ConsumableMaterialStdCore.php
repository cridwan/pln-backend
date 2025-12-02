<?php

namespace App\Core\Transaction\Detail;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\ConsMat;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ConsumableMaterialStdCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'consmat.globalUnit'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return ConsMat::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Consmat::query()
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
