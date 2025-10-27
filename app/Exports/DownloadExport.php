<?php

namespace App\Exports;

use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DownloadExport implements FromQuery, WithChunkReading, WithHeadings, WithStyles, WithMapping, ShouldAutoSize
{
    use Exportable;

    private $instanceModel;

    /**
     * @param mixed $query
     * @param mixed $with
     * @param \App\Data\AttributeData[] $customAttribute
     */
    public function __construct(private mixed $query, private readonly mixed $with, private readonly array $customAttribute = [])
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query->with($this->with);
    }

    public function map($row): array
    {
        if (count($this->customAttribute) > 0) {
            return $this->customAttributeMap($row);
        }
        return $this->baseMap($row);
    }

    private function customAttributeMap($row)
    {
        $base = [];
        foreach ($this->customAttribute as $attribute) {
            $base[] = is_callable($attribute->key) ? ($attribute->key)($row) : $row->{$attribute->key};
        }

        return $base;
    }

    private function baseMap($row)
    {
        $base = [];

        // isi kolom dari table
        foreach ($this->getAttributes() as $attr) {
            if (!str_ends_with($attr, '_uuid') && !str_ends_with($attr, '_at')) {
                $base[$attr] = $row->{$attr};
            }
        }

        // Relasi dinamis
        foreach ((array) $this->with as $relation) {
            $parts = explode('.', $relation);

            $relatedModel = $row;
            foreach ($parts as $part) {
                $relatedModel = $relatedModel?->$part;
            }

            // pakai nama relasi terakhir sebagai key
            $last = end($parts);
            $base[$last] = $relatedModel?->name ?? null;
        }

        foreach ($this->getAttributes() as $attr) {
            if (!str_ends_with($attr, '_uuid') && str_ends_with($attr, '_at')) {
                $base[$attr] = $row->{$attr};
            }
        }

        return $base;
    }

    public function headings(): array
    {
        if (count($this->customAttribute) > 0) {
            return $this->customHeadings();
        }

        return $this->baseHeadings();
    }

    private function customHeadings()
    {
        $columns = [];

        foreach ($this->customAttribute as $attribute) {
            $columns[] = $attribute->label;
        }

        return $columns;
    }

    private function baseHeadings()
    {
        $columns = [];

        foreach ((array) $this->getAttributes() as $attribute) {
            if (!str_ends_with($attribute, '_uuid') && !str_ends_with($attribute, '_at')) {
                $columns[] = join(' ', explode('_', strtoupper($attribute)));
            }
        }

        foreach ((array) $this->with as $relation) {
            $parts = explode('.', $relation);

            $columns[] = strtoupper(end($parts));
        }

        foreach ((array) $this->getAttributes() as $attribute) {
            if (!str_ends_with($attribute, '_uuid') && str_ends_with($attribute, '_at')) {
                $columns[] = join(' ', explode('_', strtoupper($attribute)));
            }
        }

        return $columns;
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
        $convertColumn = $this->numberToAlphabet(count($this->headings()));
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
