<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorPatientsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Patients';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PATIENTS LIST'];
        $rows[] = ['Total Unique Patients', $this->data['total'] ?? 0];
        $rows[] = [];
        $rows[] = ['#', 'Patient Name', 'Phone', 'Email', 'Total Visits', 'Total Spent', 'First Visit', 'Last Visit'];

        foreach ($this->data['patients'] ?? [] as $i => $p) {
            $rows[] = [
                $i + 1,
                $p['patient_name'] ?? ($p['user']['username'] ?? '-'),
                $p['user']['phone'] ?? '-',
                $p['user']['email'] ?? '-',
                $p['visits_count'] ?? 0,
                'EGP ' . number_format($p['total_spent'] ?? 0, 2),
                isset($p['first_visit']) ? substr($p['first_visit'], 0, 10) : '-',
                isset($p['last_visit']) ? substr($p['last_visit'], 0, 10) : '-',
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
