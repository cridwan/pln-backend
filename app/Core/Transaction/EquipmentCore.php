<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\TemplateData;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\Equipment;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class EquipmentCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'scopeStandart'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'name',
            'asc',
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Equipment::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Equipment::query()
            ->has('scopeStandart.project');
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function search(): array
    {
        return [
            'name',
        ];
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
