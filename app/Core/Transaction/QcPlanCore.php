<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\QcPlan;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class QcPlanCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'document',
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
        return QcPlan::class;
    }

    #[DoNotDiscover]
    public function rules(): array
    {
        return [
            "name" => "required",
        ];
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
        return new TemplateData([
            'name',
        ]);
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
