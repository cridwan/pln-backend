<?php

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Exports\TemplateExport;
use App\Http\Requests\ImportRequest;
use App\Imports\BulkDataImport;
use Illuminate\Support\Facades\Schema;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

trait ImportExportExcel
{
    #[Route(method: 'post', name: "export/excel")]
    public function export()
    {
        if (!$this->model) {
            throw new BadRequestException('Model not defined');
        }

        $filteredAttributes = $this->filteredAttributes();

        $tableName = $this->getTableName();

        return (new TemplateExport($filteredAttributes))->download("Template data $tableName - " . date("YmdHis") . ".xlsx");
    }

    #[Route(method: 'post', name: "import/excel")]
    public function import(ImportRequest $request)
    {
        if (!\class_exists($this->model)) {
            throw new BadRequestException('Model cannot be found');
        }

        (new BulkDataImport($this->model, $this->filteredAttributes()))->import($request->file('file'), 'local', \Maatwebsite\Excel\Excel::XLSX);

        return [
            'message' => 'Import data successfully'
        ];
    }


    #[DoNotDiscover]
    public function getAttributes()
    {
        return Schema::getColumnListing($this->getTableName());
    }

    #[DoNotDiscover]
    public function getTableName()
    {
        return (new $this->model)->getTable();
    }

    #[DoNotDiscover]
    public function filteredAttributes()
    {
        return array_values(array_diff($this->getAttributes(), [
            'created_at',
            'updated_at',
            'id',
            'uuid'
        ]));
    }
}
