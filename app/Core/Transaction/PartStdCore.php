<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\TemplateData;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\Part;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class PartStdCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'name',
            'asc'
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Part::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Part::query()
            ->has('activity.equipment.scopeStandart.project');
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
