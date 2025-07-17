<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkDataImport implements ToModel, WithChunkReading, WithHeadingRow
{
    use Importable;

    public function __construct(public string $model, public array $headings) {}

    public function model(array $row)
    {
        $model = $this->model;

        $mapData = $this->mapData($row);

        // Create langsung di sini
        return new $model($mapData);
    }

    public function mapData(array $row)
    {
        $data = [];

        foreach ($this->headings as $heading) {
            if (!array_key_exists($heading, $row)) {
                throw new \App\Exceptions\BadRequestException("Kolom '$heading' tidak ditemukan dalam file Excel.");
            }

            $data[$heading] = $row[$heading];
        }

        return $data;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
