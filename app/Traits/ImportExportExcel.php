<?php

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Exports\DownloadExport;
use App\Exports\TemplateExport;
use App\Http\Requests\ImportRequest;
use App\Imports\BulkDataImport;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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
        $customAttributes = isset($this->attributeTemplate) ? $this->attributeTemplate : null;

        $tableName = $this->getTableName();

        return (new TemplateExport($filteredAttributes, $customAttributes))->download("Template data $tableName - " . date("YmdHis") . ".xlsx");
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

        $customAttributes = isset($this->attributeExport) ? $this->attributeExport : [];

        return (new DownloadExport($this->model, $with, $customAttributes))->download("Template data $tableName - " . date("YmdHis") . ".xlsx");
    }

    /**
     * import data
     */
    #[Route(method: 'post', name: "import/excel")]
    public function import(ImportRequest $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:15000', // Validate file type and size
        ]);

        try {
            DB::beginTransaction();

            if (!\class_exists($this->model)) {
                throw new BadRequestException('Model cannot be found');
            }

            $callback = method_exists($this, 'mapping') ? [$this, 'mapping'] : null;

            (new BulkDataImport($this->model, $this->filteredAttributes(), $callback))
                ->import($request->file('file'), 'local', \Maatwebsite\Excel\Excel::XLSX);
            DB::commit();
            return [
                'message' => 'Import data successfully'
            ];
        } catch (QueryException $e) {
            DB::rollBack();
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
            DB::rollBack();
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
        // $except = match ($this->model) {
        //     "App\Models\ScopeStandart" => 'additional_scope_uuid',
        //     default => '',
        // };
        return array_values(array_diff($this->getAttributes(), [
            'created_at',
            'updated_at',
            'id',
            'uuid',
            // $except,
        ]));
    }
}
