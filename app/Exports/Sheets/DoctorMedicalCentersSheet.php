<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorMedicalCentersSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Medical Centers';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['MEDICAL CENTERS'];
        $rows[] = ['Total Centers',  $this->data['total'] ?? 0];
        $rows[] = ['Primary Center', $this->data['primary']['name'] ?? 'N/A'];
        $rows[] = [];
        $rows[] = ['#', 'Name', 'Address', 'Phone', 'Email', 'Latitude', 'Longitude', 'Active', 'Primary'];

        $primaryId = $this->data['primary']['id'] ?? null;
        foreach ($this->data['centers'] ?? [] as $i => $c) {
            $rows[] = [
                $i + 1,
                $c['name'] ?? '-',
                $c['address'] ?? '-',
                $c['phone'] ?? '-',
                $c['email'] ?? '-',
                $c['latitude'] ?? '-',
                $c['longitude'] ?? '-',
                isset($c['is_active']) ? ($c['is_active'] ? 'Yes' : 'No') : '-',
                ($primaryId && $c['id'] === $primaryId) ? 'Yes' : 'No',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            5 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
