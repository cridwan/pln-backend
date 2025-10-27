<?php

namespace App\Exports;

use App\Data\OptionData;
use App\Data\TemplateData;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TemplateExport implements WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    use Exportable;
    public function __construct(
        public array $headers,
        public ?TemplateData $customAttributes = null,
    ) {
    }

    public function headings(): array
    {
        if ($this->customAttributes) {
            return $this->customAttributes->headers;
        }

        return $this->headers;
    }

    public function map($row): array
    {
        return [];
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

    public function styles(Worksheet $sheet)
    {
        $convertColumn = $this->numberToAlphabet(count($this->headings()));
        $range = "A1:{$convertColumn}5";

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

    public function registerEvents(): array
    {
        return [
                // handle by a closure.
            AfterSheet::class => function (AfterSheet $event) {
                if ($this->customAttributes) {
                    if ($this->customAttributes->options instanceof OptionData) {
                        $sheet = $event->sheet->getDelegate();
                        $row_count = 1000 + 1; // misal maksimal 1000 baris input
                        $column = $this->customAttributes->options->column;

                        // Taruh data dropdown di sheet tersembunyi
                        $hiddenSheet = $sheet->getParent()->createSheet();
                        $hiddenSheet->setTitle('dropdown_values');

                        $options = $this->customAttributes->options->options;

                        // Isi data ke kolom A di sheet tersembunyi
                        foreach ($options as $index => $name) {
                            $hiddenSheet->setCellValue("A" . ($index + 1), $name);
                        }

                        // Buat sheet tersembunyi
                        $hiddenSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                        // Range referensi dropdown
                        $range = '=dropdown_values!$A$1:$A$' . count($options);

                        // Buat dropdown di setiap baris di sheet utama
                        for ($row = 2; $row <= $row_count; $row++) {
                            $cell = "{$column}{$row}";

                            $validation = $sheet->getCell($cell)->getDataValidation();
                            $validation->setType(DataValidation::TYPE_LIST);
                            $validation->setErrorStyle(DataValidation::STYLE_STOP);
                            $validation->setAllowBlank(true);
                            $validation->setShowDropDown(true);
                            $validation->setFormula1($range);
                            $validation->setShowErrorMessage(true);
                            $validation->setErrorTitle('Invalid value');
                            $validation->setError('Please select from the list.');
                        }
                    } else if (is_array($this->customAttributes->options)) {
                        /** @var OptionData[] $attributeOptions */
                        $attributeOptions = $this->customAttributes->options;
                        foreach ($attributeOptions as $indexOpt => $option) {
                            $sheet = $event->sheet->getDelegate();
                            $row_count = 1000 + 1; // misal maksimal 1000 baris input
                            $column = $option->column;

                            // Taruh data dropdown di sheet tersembunyi
                            $hiddenSheet = $sheet->getParent()->createSheet();
                            $hiddenSheet->setTitle('dropdown_values_' . $indexOpt);

                            $options = $option->options;

                            // Isi data ke kolom A di sheet tersembunyi
                            foreach ($options as $index => $name) {
                                $hiddenSheet->setCellValue("A" . ($index + 1), $name);
                            }

                            // Buat sheet tersembunyi
                            $hiddenSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

                            // Range referensi dropdown
                            $range = '=dropdown_values_' . $indexOpt . '!$A$1:$A$' . count($options);

                            // Buat dropdown di setiap baris di sheet utama
                            for ($row = 2; $row <= $row_count; $row++) {
                                $cell = "{$column}{$row}";

                                $validation = $sheet->getCell($cell)->getDataValidation();
                                $validation->setType(DataValidation::TYPE_LIST);
                                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                                $validation->setAllowBlank(true);
                                $validation->setShowDropDown(true);
                                $validation->setFormula1($range);
                                $validation->setShowErrorMessage(true);
                                $validation->setErrorTitle('Invalid value');
                                $validation->setError('Please select from the list.');
                            }
                        }
                    }
                }
            },
        ];
    }
}
