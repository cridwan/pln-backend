<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionTemplateExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;

    private int $count = 0;

    public function __construct(
        private readonly array $headers,
        private readonly Builder|Relation $query,
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return $this->headers;
    }

    public function map($row): array
    {
        $this->count++;
        return $this->mapData($row);
    }

    public function mapData(mixed $row)
    {
        // convert to array kalau dia Collection
        $row = $row->toArray();

        $data = [];

        foreach ($this->headers as $heading) {
            $value = data_get($row, $heading);

            if (is_null($value)) {
                throw new \App\Exceptions\BadRequestException("Kolom '$heading' tidak ditemukan dalam hasil query.");
            }

            $data[] = $value;
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->headers);
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
        $lastRow = $this->count + 1; // +1 untuk heading

        $range = "A1:{$lastColumn}{$lastRow}";

        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // warna hitam
                ]
            ]
        ]);

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
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
