<?php

namespace App\Imports;

use App\Exceptions\BadRequestException;
use Exception;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkDataImport implements ToModel, WithChunkReading, WithHeadingRow
{
    private int $row = 0;
    use Importable;

    /**
     * Summary of __construct
     * @param string $model
     * @param array $headings
     * @param \App\Data\AttributeData[] $customAttribute
     */
    public function __construct(public string $model, public array $headings, public mixed $callback = null)
    {
    }

    public function model(array $row)
    {
        $this->row++;
        try {
            if (is_callable($this->callback)) {
                call_user_func($this->callback, $row, $this->row);
            } else {
                $this->mapData($row);
            }
        } catch (\Throwable $th) {
            throw $th;
            $values = array_values($row);
            if (str($th->getMessage())->contains('Duplicate entry') || str($th->getMessage())->contains('Duplicate')) {
                throw new Exception("[Duplicate]: Data (" . join(', ', $values) . ") sudah di tambahkan pada baris ke {$this->row}");
            }

            if ($th instanceof BadRequestException) {
                throw new Exception($th->getMessage());
            }

            throw new Exception("[Error]: Gagal insert " . join(', ', $values) . " pada baris ke {$this->row}");
        }
    }

    public function mapData(array $row)
    {
        $data = [];
        foreach ($this->headings as $heading) {
            if (!array_key_exists($heading, $row)) {
                throw new BadRequestException("[Mapping]: Gagal mapping data. Pastikan anda menggunakan format excel yang sudah di sediakan");
            }

            if (isset($row[$heading]) && trim((string) $row[$heading]) !== '') {
                $data[$heading] = $row[$heading];
            }
        }

        $modelClass = $this->model;

        // Jika baris kosong, skip
        if (empty($data) || count($data) == 0) {
            return null;
        }

        return $modelClass::create($data);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @param  string|\Symfony\Component\HttpFoundation\File\UploadedFile|null  $file
     * @param  string|null  $disk
     * @param  string|null  $readerType
     * @return \Maatwebsite\Excel\Importer|\Illuminate\Foundation\Bus\PendingDispatch
     *
     * @throws \Maatwebsite\Excel\Exceptions\NoFilePathGivenException
     */
    public function importWithTransaction($file = null, ?string $disk = null, ?string $readerType = null)
    {
        DB::beginTransaction();
        try {
            $importer = $this->import($file);
            DB::commit();
            return $importer;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
