<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorTreatmentsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Treatments';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['SELECTED TREATMENTS'];
        $rows[] = ['Total Treatments', $this->data['total'] ?? 0];
        $rows[] = [];
        $rows[] = ['#', 'Treatment Name', 'Company'];

        foreach ($this->data['treatments'] ?? [] as $i => $t) {
            $rows[] = [
                $i + 1,
                $t['name'] ?? '-',
                $t['company'] ?? '-',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            4 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
