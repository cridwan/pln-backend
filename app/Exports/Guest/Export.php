<?php

namespace App\Exports\Guest;

use App\Enums\ExportTypeEnum;
use App\Models\InspectionType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
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

abstract class Export implements FromQuery, WithDrawings, WithMapping, WithStyles, WithTitle, WithCustomStartCell
{
    use Exportable;
    protected string $title = 'PLN IP UBH';
    protected string $fileName = "export";

    public function __construct(public readonly InspectionType $InspectionType, private readonly ExportTypeEnum $exportType = ExportTypeEnum::XLSX)
    {
    }

    abstract public function query();

    abstract public function headers(): array;

    public function inspection(): string
    {
        return $this->InspectionType?->name ?? '';
    }

    public function machine(): string
    {
        return $this->InspectionType?->machine?->name ?? '';
    }

    public function map($row): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function startCell(): string
    {
        return 'A7'; // Data dimulai di bawah header
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

    private function setHeader(Worksheet $sheet)
    {
        $sheet->setCellValue('B1', 'PT. PLN INDONESIA POWER');
        $sheet->setCellValue('B2', 'SUMMARY SCOPE STANDARD PEMELIHARAAN PERIODIK');
        $sheet->setCellValue('B3', $this->inspection());
        $sheet->setCellValue('B4', $this->machine());
    }

    private function numberToAlpha(int $number)
    {
        return chr($number + 64);
    }

    private function setHeadings(Worksheet $sheet)
    {
        foreach ($this->headers() as $index => $header) {
            $col = $this->numberToAlpha($index + 1); // 0 = A, 1 = B, dst
            $sheet->setCellValue("{$col}5", $header);

            if (str($col)->upper() != 'A') {
                $sheet->getColumnDimension($col)->setWidth(20);
            }

            $sheet->mergeCells("{$col}5:{$col}6");
        }
    }

    public function styles(Worksheet $sheet)
    {
        // Ambil baris terakhir (biar tahu seberapa panjang data)
        $lastRow = $sheet->getHighestRow();

        $lastAlpha = $this->numberToAlpha(count($this->headers()));

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->mergeCells('A1:A4');
        $sheet->mergeCells("B1:{$lastAlpha}1");
        $sheet->mergeCells("B2:{$lastAlpha}2");
        $sheet->mergeCells("B3:{$lastAlpha}3");
        $sheet->mergeCells("B4:{$lastAlpha}4");

        // Menulis header langsung ke dalam Excel
        $this->setHeader($sheet);
        $this->setHeadings($sheet);
        // detail

        // bold title
        $sheet->getStyle("A1:{$lastAlpha}6")->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'font' => [
                'bold' => true,
            ],
        ]);

        // background
        $sheet->getStyle("B1:{$lastAlpha}1")->applyFromArray([
            'fill' => array(
                'fillType' => Fill::FILL_SOLID, // Gunakan FILL_SOLID agar warna tampil dengan jelas
                'startColor' => [
                    'rgb' => 'A1E3F9' // Warna merah
                ]
            )
        ]);

        $sheet->getStyle("A5:{$lastAlpha}6")->applyFromArray([
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
        $sheet->getStyle("A1:{$lastAlpha}{$lastRow}")->applyFromArray([
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

    public function getFileName()
    {
        return $this->exportType == ExportTypeEnum::XLSX ? now()->toDateString() . '-' . $this->fileName . '.xlsx' : now()->toDateString() . '-' . $this->fileName . '.pdf';
    }

    private function pdfData()
    {
        return [
            'inspection' => $this->inspection(),
            'machine' => $this->machine(),
            'exporter' => $this->getExporter()
        ];
    }

    private function processPdf()
    {
        return Pdf::loadView('export.pdf.horizontal', $this->pdfData())
            ->setPaper('A4')
            ->download($this->getFileName());
    }

    public function execute()
    {
        return $this->download($this->getFileName());
    }

}
