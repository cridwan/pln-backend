<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DownloadExport implements FromQuery, WithChunkReading, WithHeadings, WithStyles
{
    use Exportable;
    private $instanceModel;
    public function __construct(private readonly string $model)
    {
        $this->instanceModel = new $model;
    }
    public function query()
    {
        return $this->model::query();
    }

    public function headings(): array
    {
        return $this->getAttributes();
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function numberToAlphabet($num)
    {
        $alphabet = '';
        while ($num > 0) {
            $remainder = ($num - 1) % 26;
            $alphabet = chr(65 + $remainder) . $alphabet; // 65 = 'A', kalau mau lowercase pakai 97
            $num = intval(($num - 1) / 26);
        }
        return strtolower($alphabet); // pakai strtolower kalau mau a-z kecil
    }

    private function getAttributes()
    {
        return Schema::getColumnListing($this->instanceModel->getTable());
    }

    public function styles(Worksheet $sheet)
    {
        $convertColumn = $this->numberToAlphabet(count($this->getAttributes()));
        $count = $this->query()->count() + 1;
        $range = "A1:{$convertColumn}{$count}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // warna hitam
                ]
            ]
        ]);

        $sheet->getStyle("A1:{$convertColumn}1")->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => '273F4F', // warna kuning emas (gold)
                ],
            ],
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'], // warna hitam
            ],
        ]);
    }
}
