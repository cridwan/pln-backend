<?php

namespace App\Core\Transaction;

use App\Core\TransactionCore;
use App\Data\AttributeData;
use App\Data\TemplateData;
use App\Exceptions\BadRequestException;
use App\Interfaces\WithImportExcel;
use App\Models\Transaction\AdditionalScope;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;

abstract class AdditionalScopeCore extends TransactionCore implements WithImportExcel
{
    #[DoNotDiscover]
    public function with(): array
    {
        return [
            'assetWelnes.document',
            'ohRecom.document',
            'woPriority.document',
            'history.document',
            'rla.document',
            'ncr.document'
        ];
    }

    #[DoNotDiscover]
    public function order(): array
    {
        return [
            'created_at',
            'asc',
        ];
    }

    #[DoNotDiscover]
    public function model(): string
    {
        return AdditionalScope::class;
    }

    #[DoNotDiscover]
    public function query(): mixed
    {
        return AdditionalScope::query();
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
