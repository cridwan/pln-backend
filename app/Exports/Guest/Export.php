<?php

namespace App\Exports\Guest;

use App\Enums\ExportTypeEnum;
use App\Models\InspectionType;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class Export implements FromQuery, WithDrawings, WithMapping, WithStyles, WithTitle, WithCustomStartCell, WithHeadings, WithEvents
{
    use Exportable;
    protected string $title = 'PLN IP UBH';
    protected string $fileName = "export";

    public function __construct(public readonly InspectionType $InspectionType, private readonly ExportTypeEnum $exportType = ExportTypeEnum::XLSX)
    {
    }


    abstract public function query();

    abstract public function headings(): array;

    public function inspection(): string
    {
        return ($this->InspectionType?->name ?? '') . ' / ' . ($this->InspectionType?->machine?->name ?? '') . ' / ' . ($this->InspectionType?->machine?->unit?->name ?? '') . ' / ' . ($this->InspectionType?->machine?->unit?->location?->name ?? '');
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
        return 'A5'; // Data dimulai di bawah header
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
        // $sheet->setCellValue('B4', $this->machine());
    }

    private function numberToAlpha(int $number)
    {
        return chr($number + 64);
    }

    private function setHeadings(Worksheet $sheet)
    {
        foreach ($this->headings() as $index => $header) {
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
        $highestColumn = $sheet->getHighestColumn();

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->mergeCells('A1:A4');
        $sheet->mergeCells("B1:{$highestColumn}1");
        $sheet->mergeCells("B2:{$highestColumn}2");
        $sheet->mergeCells("B3:{$highestColumn}4");
        // $sheet->mergeCells("B4:{$highestColumn}4");

        // Menulis header langsung ke dalam Excel
        $this->setHeader($sheet);
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();

                $sheet->getColumnDimension('A')->setWidth(20);

                // auto size column
                foreach (range('B', $sheet->getHighestColumn()) as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // style header
                $headerRows = is_array($this->headings()[0])
                    ? count($this->headings()) + 4
                    : 5;

                $headerRange = 'A5:' . $sheet->getHighestColumn() . $headerRows;
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '000000'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'A1E3F9'],
                    ],
                ]);

                // ✅ Terapkan border ke semua data (header + isi)
                $dataRange = "A1:{$highestColumn}{$highestRow}";
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Tinggi baris header
                for ($i = 1; $i <= $headerRows; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(22);
                    // Atur alignment isi di baris tersebut
                    $cell = $sheet->getStyle("A{$i}:" . $sheet->getHighestColumn() . "{$i}");
                    $cell->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                        ->setWrapText(true); // supaya teks panjang otomatis pindah baris
                    $cell->getFont()->setBold(true);
                }
            },
        ];
    }

}
