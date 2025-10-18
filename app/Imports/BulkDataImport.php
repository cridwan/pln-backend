<?php

namespace App\Imports;

use App\Exceptions\BadRequestException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkDataImport implements ToModel, WithChunkReading, WithHeadingRow
{
    private int $row = 0;
    use Importable;

    public function __construct(public string $model, public array $headings)
    {
    }

    public function model(array $row)
    {
        $modelClass = $this->model;
        $mapData = $this->mapData($row);

        \Log::info("before insert", $mapData);
        // Jika baris kosong, skip
        if (empty($mapData) || count($mapData) == 0) {
            return null;
        }

        // ❗ VALIDASI WAJIB: Jika kolom penting kosong, lempar error
        // if (empty($mapData['name'])) {
        //     throw new \Exception("Nama tidak boleh kosong");
        // }

        // ✅ Insert manual agar bisa rollback total jika error di controller
        try {
            $this->row++;
            return $modelClass::create($mapData);
        } catch (\Throwable $e) {
            \Log::info($e->getMessage());
            // Lempar error agar transaksi berhenti → rollback di controller
            $values = array_values($mapData);
            if (str($e->getMessage())->contains('Duplicate entry')) {
                throw new \Exception("[Duplicate]: Data (" . join(', ', $values) . ") sudah di tambahkan pada baris ke {$this->row}");
            }

            throw new \Exception("[Error]: Terjadi kesalahan ketika insert " . join(', ', $values) . " pada baris ke {$this->row}");
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
        return $data;
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
