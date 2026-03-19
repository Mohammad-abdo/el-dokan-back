<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DoctorPrescriptionsSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private array $data) {}

    public function title(): string
    {
        return 'Prescriptions';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['PRESCRIPTIONS SUMMARY'];
        $rows[] = ['Total',     $this->data['total'] ?? 0];
        $rows[] = ['Shared',    $this->data['shared'] ?? 0];
        $rows[] = ['Not Shared', $this->data['not_shared'] ?? 0];
        $rows[] = ['Templates', $this->data['templates_count'] ?? 0];
        $rows[] = [];

        $rows[] = ['ALL PRESCRIPTIONS'];
        $rows[] = [
            '#', 'Prescription No.', 'Prescription Name',
            'Patient Name', 'Patient Phone',
            'Notes', 'Shared', 'Date',
        ];

        foreach ($this->data['recent'] ?? [] as $i => $p) {
            $rows[] = [
                $i + 1,
                $p['prescription_number'] ?? '-',
                $p['prescription_name'] ?? '-',
                $p['patient_name'] ?? ($p['patient']['username'] ?? '-'),
                $p['patient_phone'] ?? ($p['patient']['phone'] ?? '-'),
                $p['notes'] ?? '-',
                ($p['is_shared'] ?? false) ? 'Yes' : 'No',
                isset($p['created_at']) ? substr($p['created_at'], 0, 10) : '-',
            ];

            // Inline items for this prescription
            if (!empty($p['items'])) {
                $rows[] = ['', '', '  → Medication', 'Dosage', 'Qty', 'Duration (days)', 'Price', 'Status', 'Instructions'];
                foreach ($p['items'] as $item) {
                    $rows[] = [
                        '', '', '  ' . ($item['medication_name'] ?? '-'),
                        $item['dosage'] ?? '-',
                        $item['quantity'] ?? '-',
                        $item['duration_days'] ?? '-',
                        'EGP ' . number_format($item['price'] ?? 0, 2),
                        ucfirst($item['status'] ?? '-'),
                        $item['instructions'] ?? '-',
                    ];
                }
                $rows[] = [];
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF1E40AF']]],
            7 => ['font' => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FF1E40AF']]],
            8 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E40AF']]],
        ];
    }
}
