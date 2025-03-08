<?php

namespace App\Exports;

use App\Models\Transaction\AdditionalScope;
use App\Models\Transaction\ScopeStandart;
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

class ScopeStandartExport implements FromQuery, WithMapping, WithColumnWidths, WithStyles, WithDrawings, WithCustomStartCell, WithTitle
{
    public function __construct(
        protected string $category,
        protected string $title
    ) {}

    public function query()
    {
        $category = $this->category;
        return ScopeStandart::query()
            ->with(['details', 'assetWelnes', 'ohRecom', 'woPriority', 'history', 'rla', 'ncr'])
            ->where('category', 'like', "%$category%")
            ->union(
                AdditionalScope::query()
                    ->with(['details', 'assetWelnes', 'ohRecom', 'woPriority', 'history', 'rla', 'ncr'])
                    ->has('assetWelnes')
                    ->orHas('ohRecom')
                    ->orHas('woPriority')
                    ->orHas('history')
                    ->orHas('rla')
                    ->orHas('ncr')
            );
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
        $drawing->setOffsetX(60); // Jarak dari kiri
        $drawing->setOffsetY(60); // Jarak dari atas

        return $drawing;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 100,
            'C' => 35,
            'D' => 35,
            'E' => 35,
            'F' => 35,
            'G' => 35,
            'H' => 35,
        ];
    }

    public function startCell(): string
    {
        return 'A7'; // Data dimulai di bawah header
    }

    public function map($row): array
    {
        $data =  [];

        if (count($row->details) > 0) {
            foreach ($row->details as $index => $item) {
                if ($index == 0) {
                    $data[] = [
                        $row->name,
                        $item->name,
                        $row->assetWelnes->note ?? '-',
                        $row->ohRecom->note ?? '-',
                        $row->woPriority->note ?? '-',
                        $row->history->note ?? '-',
                        $row->rla->note ?? '-',
                        $row->ncr->note ?? '-',
                    ];
                } else {
                    $data[] = [
                        '',
                        $item->name,
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ];
                }
            }
        } else {
            $data[] = [
                $row->name,
                '-',
                $row->assetWelnes->note ?? '-',
                $row->ohRecom->note ?? '-',
                $row->woPriority->note ?? '-',
                $row->history->note ?? '-',
                $row->rla->note ?? '-',
                $row->ncr->note ?? '-',
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Ambil baris terakhir (biar tahu seberapa panjang data)
        $lastRow = $sheet->getHighestRow();
        $this->autoMerged($sheet, $lastRow, 'A', 'A');
        $this->autoMerged($sheet, $lastRow, 'C', 'H');

        // Menulis header langsung ke dalam Excel
        $sheet->setCellValue('B1', 'PT. PLN INDONESIA POWER');
        $sheet->setCellValue('B2', 'SUMMARY SCOPE STANDARD PEMELIHARAAN PERIODIK');
        $sheet->setCellValue('B3', 'MAJOR INSPECTION');
        $sheet->setCellValue('B4', 'GAS TURBINE (M70 F)');
        // detail
        $sheet->setCellValue('A5', 'SCOPE');
        $sheet->setCellValue('B5', 'DETAIL');
        $sheet->setCellValue('C5', 'CONDITION');
        $sheet->setCellValue('C6', 'ASSET WELNESS');
        $sheet->setCellValue('D6', 'OH RECOMENDATION');
        $sheet->setCellValue('E6', 'WO PRIORITY');
        $sheet->setCellValue('F6', 'HISTORY');
        $sheet->setCellValue('G6', 'RLA');
        $sheet->setCellValue('H6', 'NCR');


        $sheet->mergeCells('A1:A4');
        $sheet->mergeCells('B1:H1');
        $sheet->mergeCells('B2:H2');
        $sheet->mergeCells('B3:H3');
        $sheet->mergeCells('B4:H4');
        $sheet->mergeCells('C5:H5');
        $sheet->mergeCells('A5:A6');
        $sheet->mergeCells('B5:B6');

        // bold title
        $sheet->getStyle('A1:H4')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        $sheet->getStyle('A5:B6')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        $sheet->getStyle('C5:H5')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // background
        $sheet->getStyle('B1:H1')->applyFromArray([
            'fill' => array(
                'fillType' => Fill::FILL_SOLID, // Gunakan FILL_SOLID agar warna tampil dengan jelas
                'startColor' => [
                    'rgb' => 'A1E3F9' // Warna merah
                ]
            )
        ]);

        $sheet->getStyle('A5:H6')->applyFromArray([
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
        $sheet->getStyle("A1:H$lastRow")->applyFromArray([
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

        // BORDER
        $sheet->getStyle("A1:A$lastRow")->applyFromArray([
            'alignment' => [
                'wrapText' => true,
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
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
