<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\TemplateData;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\Activity;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class ActivityCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'equipment',
            'document'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'serial_number',
            'asc',
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return Activity::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return Activity::query()
            ->has('equipment.scopeStandart.project');
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
