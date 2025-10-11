<?php

namespace App\Imports;

use App\Exceptions\BadRequestException;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkDataImport implements ToModel, WithChunkReading, WithHeadingRow
{
    use Importable;

    public function __construct(public string $model, public array $headings)
    {
    }

    public function model(array $row)
    {
        $model = $this->model;

        $mapData = $this->mapData($row);

        if (count($mapData) == 0) {
            throw new BadRequestException("Format excel tidak valid, pastikan anda menggunakan format excel yang sudah di sediakan");
        }

        // Create langsung di sini
        return new $model($mapData);
    }

    public function mapData(array $row)
    {
        $data = [];

        foreach ($this->headings as $heading) {
            if (!array_key_exists($heading, $row)) {
                throw new BadRequestException("Pastikan anda menggunakan format excel yang sudah di sediakan");
            }

            if (isset($row[$heading]) && trim((string) $row[$heading]) !== '') {
                $data[$heading] = $row[$heading];
            }
        }

        return $data;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
