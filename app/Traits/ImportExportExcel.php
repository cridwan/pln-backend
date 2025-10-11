<?php

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Exports\DownloadExport;
use App\Exports\TemplateExport;
use App\Http\Requests\ImportRequest;
use App\Imports\BulkDataImport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Spatie\RouteDiscovery\Attributes\DoNotDiscover;
use Spatie\RouteDiscovery\Attributes\Route;

trait ImportExportExcel
{
    /**
     * get template import data
     */
    #[Route(method: 'post', name: "export/template")]
    public function template()
    {
        if (!$this->model) {
            throw new BadRequestException('Model not defined');
        }

        $filteredAttributes = $this->filteredAttributes();

        $tableName = $this->getTableName();

        return (new TemplateExport($filteredAttributes))->download("Template data $tableName - " . date("YmdHis") . ".xlsx");
    }

    /**
     * download data excel
     */
    #[Route(method: 'post', name: "export/excel")]
    public function export()
    {
        if (!$this->model) {
            throw new BadRequestException('Model not defined');
        }

        $tableName = $this->getTableName();

        $with = isset($this->with) ? $this->with : [];

        return (new DownloadExport($this->model, $with))->download("Template data $tableName - " . date("YmdHis") . ".xlsx");
    }

    /**
     * import data
     */
    #[Route(method: 'post', name: "import/excel")]
    public function import(ImportRequest $request)
    {
        try {
            if (!\class_exists($this->model)) {
                throw new BadRequestException('Model cannot be found');
            }

            (new BulkDataImport($this->model, $this->filteredAttributes()))->import($request->file('file'), 'local', \Maatwebsite\Excel\Excel::XLSX);

            return [
                'message' => 'Import data successfully'
            ];
        } catch (QueryException $e) {
            // Tangkap Duplicate Entry
            if ($e->getCode() == 23000) {
                $message = $e->getMessage();

                // Regex untuk ambil bagian dalam tanda kutip Duplicate entry '___'
                if (preg_match("/Duplicate entry '([^']+)'/", $message, $matches)) {
                    $duplicateValue = $matches[1];  // ADIPALA--77219-109183400
                    throw new BadRequestException("[Duplicate] data sudah di tambahkan: {$duplicateValue}");
                }

                throw new BadRequestException("Terjadi duplikat data.");
            }

            throw new BadRequestException($e->getMessage());
        } catch (\Throwable $th) {
            throw $th;
        }
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
