<?php

namespace App\Exports;

use App\Models\Transaction\Tools;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ToolsExport implements FromQuery, WithMapping, WithColumnWidths, WithStyles, WithDrawings, WithCustomStartCell, WithTitle
{
    protected int $index = 0;
    public function __construct(
        protected string $title,
        protected $project
    ) {}

    public function query()
    {
        return Tools::query()
            ->addSelect([
                "tools.*",
                DB::raw(
                    "('SCOPE STANDART') AS type"
                )
            ])
            ->with('globalUnit')
            ->where('project_uuid', $this->project?->uuid)
            ->union(
                Tools::query()
                    ->addSelect([
                        "tools.*",
                        DB::raw(
                            "('ADDITIONAL SCOPE') AS type"
                        )
                    ])
                    ->with('globalUnit')
                    ->whereHas('additionalScope', function ($query) {
                        $query->has('assetWelnes')
                            ->orHas('ohRecom')
                            ->orHas('woPriority')
                            ->orHas('history')
                            ->orHas('rla')
                            ->orHas('ncr')
                            ->where('project_uuid', $this->project?->uuid);
                    })
            )->orderBy('type', 'DESC');
    }

    public function title(): string
    {
        return $this->title;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo Perusahaan');
        $drawing->setDescription('Ini adalah logo perusahaan.');
        $drawing->setPath(public_path('logo.png')); // Path gambar di folder public
        $drawing->setHeight(30); // Tinggi gambar dalam pixel
        $drawing->setWidth(150);
        $drawing->setCoordinates('A1'); // Menentukan sel tempat gambar dimulai
        $drawing->setOffsetX(10); // Jarak dari kiri
        $drawing->setOffsetY(10); // Jarak dari atas

        return $drawing;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 70,
            'D' => 15,
            'E' => 20,
            'F' => 20,
        ];
    }

    public function startCell(): string
    {
        return 'A7'; // Data dimulai di bawah header
    }

    public function map($row): array
    {
        return [
            ++$this->index,
            $row->type,
            $row->name ?? '-',
            $row->qty ?? '-',
            $row->section ?? '-',
            $row->globalUnit->name ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ambil baris terakhir (biar tahu seberapa panjang data)
        $lastRow = $sheet->getHighestRow();

        // Menulis header langsung ke dalam Excel
        $sheet->setCellValue('B1', 'PT. PLN INDONESIA POWER');
        $sheet->setCellValue('B2', 'SUMMARY SCOPE STANDARD PEMELIHARAAN PERIODIK');
        $sheet->setCellValue('B3', $this->project?->inspectionType?->name ?? '-');
        $sheet->setCellValue('B4', $this->project?->inspectionType?->machine?->name ?? '-');
        // detail
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'CATEGORY');
        $sheet->setCellValue('C5', 'URAIAN');
        $sheet->setCellValue('D5', 'VOLUME');
        $sheet->setCellValue('E5', 'SECTION');
        $sheet->setCellValue('F5', 'SATUAN');


        $sheet->mergeCells('A1:A4');
        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('B2:E2');
        $sheet->mergeCells('B3:E3');
        $sheet->mergeCells('B4:E4');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');
        $sheet->mergeCells('C5:C6');
        $sheet->mergeCells('D5:D6');
        $sheet->mergeCells('E5:E6');
        $sheet->mergeCells('F5:F6');

        // bold title
        $sheet->getStyle('A1:F6')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // background
        $sheet->getStyle('B1:F1')->applyFromArray([
            'fill' => array(
                'fillType' => Fill::FILL_SOLID, // Gunakan FILL_SOLID agar warna tampil dengan jelas
                'startColor' => [
                    'rgb' => 'A1E3F9' // Warna merah
                ]
            )
        ]);

        $sheet->getStyle('A5:F6')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => array(
                'fillType' => Fill::FILL_SOLID, // Gunakan FILL_SOLID agar warna tampil dengan jelas
                'startColor' => [
                    'rgb' => 'A1E3F9' // Warna merah
                ]
            )
        ]);

        // BORDER
        $sheet->getStyle("A1:F$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000000'], // Hitam
                ],
            ],
            'alignment' => [
                'wrapText' => true,
            ],
        ]);
    }

    public function autoMerged(Worksheet $sheet, $lastRow, $start, $end)
    {
        $mergeRanges = [];

        // Looping dari baris 6 ke bawah untuk cek cell kosong
        for ($row = 7; $row <= $lastRow; $row++) {
            foreach (range($start, $end) as $column) {
                if (empty($sheet->getCell("{$column}{$row}")->getValue())) {
                    $mergeRanges[] = "{$column}" . ($row - 1) . ":{$column}{$row}";
                }
            }
        }

        // Merge dalam satu proses agar lebih cepat
        foreach ($mergeRanges as $range) {
            $sheet->mergeCells($range);
        }
    }
}
